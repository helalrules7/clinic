<?php

namespace App\Services;

/**
 * Drug-safety checker (Feature 2 — hybrid).
 *
 * Given a newly-entered drug + a patient, returns warnings for:
 *   - ALLERGY: the drug's name/ingredient/class matches a recorded allergy
 *     (from medical_history) — deterministic, no external data.
 *   - INTERACTION: the drug's active ingredient pairs with one of the patient's
 *     current meds in the curated `drug_interactions` table (authoritative);
 *     pairs NOT in the table fall back to a Groq advisory check (source='ai',
 *     clearly "verify"). The doctor always reviews — nothing blocks prescribing.
 */
class DrugInteractionService
{
    private \PDO $pdo;
    private \PDO $drugsPdo;

    public function __construct(\PDO $pdo, \PDO $drugsPdo)
    {
        $this->pdo = $pdo;
        $this->drugsPdo = $drugsPdo;
    }

    private function norm(string $s): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim($s)));
    }

    /** Resolve a drug NAME → ['ingredient' => , 'class' => ] via the drugs DB. */
    private function resolve(string $drugName): array
    {
        $name = trim($drugName);
        $row = null;
        if ($name !== '') {
            try {
                $stmt = $this->drugsPdo->prepare('SELECT LastName AS ingredient, Pharmacology AS class FROM drugs WHERE FirstName = ? LIMIT 1');
                $stmt->execute([$name]);
                $row = $stmt->fetch();
                if (!$row) {
                    $stmt = $this->drugsPdo->prepare('SELECT LastName AS ingredient, Pharmacology AS class FROM drugs WHERE FirstName LIKE ? ORDER BY CHAR_LENGTH(FirstName) ASC LIMIT 1');
                    $stmt->execute([$name . '%']);
                    $row = $stmt->fetch();
                }
            } catch (\Throwable $e) {
                $row = null;
            }
        }
        return [
            'ingredient' => $this->norm((string) ($row['ingredient'] ?? '')),
            'class'      => $this->norm((string) ($row['class'] ?? '')),
        ];
    }

    /** Distinct drug names the patient is currently/recently prescribed. */
    private function patientMeds(int $patientId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT DISTINCT p.drug_name FROM prescriptions p
                 JOIN appointments a ON p.appointment_id = a.id
                 WHERE a.patient_id = ? AND p.drug_name IS NOT NULL AND p.drug_name <> ""'
            );
            $stmt->execute([$patientId]);
            return array_column($stmt->fetchAll(), 'drug_name');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Patient's recorded allergy strings (new entries + legacy table). */
    private function patientAllergies(int $patientId): array
    {
        $out = [];
        try {
            $stmt = $this->pdo->prepare("SELECT notes, condition_name FROM medical_history_entries WHERE patient_id = ? AND category = 'allergy'");
            $stmt->execute([$patientId]);
            foreach ($stmt->fetchAll() as $r) {
                foreach ([$r['notes'] ?? '', $r['condition_name'] ?? ''] as $t) {
                    $t = trim((string) $t);
                    if ($t !== '') {
                        $out[] = $t;
                    }
                }
            }
        } catch (\Throwable $e) {
            /* table may not exist */
        }
        try {
            $stmt = $this->pdo->prepare('SELECT allergies FROM medical_history WHERE patient_id = ? AND allergies IS NOT NULL AND allergies <> ""');
            $stmt->execute([$patientId]);
            foreach ($stmt->fetchAll() as $r) {
                $t = trim((string) ($r['allergies'] ?? ''));
                if ($t !== '') {
                    $out[] = $t;
                }
            }
        } catch (\Throwable $e) {
            /* legacy table may not exist */
        }
        return $out;
    }

    /** Allergy strings the new drug matches (name/ingredient/class vs allergy text). */
    private function allergyHits(string $drugName, array $resolved, array $allergies): array
    {
        $hits = [];
        $haystacks = array_values(array_filter([$this->norm($drugName), $resolved['ingredient'], $resolved['class']]));
        foreach ($allergies as $allergy) {
            $aNorm = $this->norm($allergy);
            if ($aNorm === '') {
                continue;
            }
            foreach (preg_split('/[^a-z]+/', $aNorm) as $tok) {
                if (strlen($tok) < 4) {
                    continue;
                }
                foreach ($haystacks as $h) {
                    if ($h !== '' && (strpos($h, $tok) !== false || strpos($tok, $h) !== false)) {
                        $hits[] = $allergy;
                        break 2;
                    }
                }
            }
        }
        return array_values(array_unique($hits));
    }

    /**
     * Curated-table lookup for a pair of resolved ingredients (order-independent).
     * The drugs DB returns full salt/combination ingredients ("diclofenac sodium",
     * "ibuprofen+pseudoephedrine") while the table is keyed on base generics — so
     * match where the table's base ingredient is a SUBSTRING of the resolved
     * ingredient (either order). Worst severity wins.
     */
    private function tableInteraction(string $a, string $b): ?array
    {
        if ($a === '' || $b === '' || $a === $b) {
            return null;
        }
        try {
            $stmt = $this->pdo->prepare(
                "SELECT severity, description FROM drug_interactions
                 WHERE (? LIKE CONCAT('%', ingredient_a, '%') AND ? LIKE CONCAT('%', ingredient_b, '%'))
                    OR (? LIKE CONCAT('%', ingredient_b, '%') AND ? LIKE CONCAT('%', ingredient_a, '%'))
                 ORDER BY FIELD(severity, 'contraindicated', 'major', 'moderate', 'minor') LIMIT 1"
            );
            $stmt->execute([$a, $b, $a, $b]);
            return $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check a newly-entered drug against the patient's allergies + current meds.
     * @return array<int, array{type:string,severity:string,drug:string,against:string,message:string,source:string}>
     */
    public function check(string $newDrugName, int $patientId): array
    {
        $warnings = [];
        $newDrugName = trim($newDrugName);
        if ($newDrugName === '' || $patientId <= 0) {
            return $warnings;
        }

        $resolved = $this->resolve($newDrugName);
        $allergies = $this->patientAllergies($patientId);
        $meds = $this->patientMeds($patientId);

        foreach ($this->allergyHits($newDrugName, $resolved, $allergies) as $allergy) {
            $warnings[] = [
                'type'     => 'allergy',
                'severity' => 'major',
                'drug'     => $newDrugName,
                'against'  => $allergy,
                'message'  => 'Patient has a recorded allergy: "' . $allergy . '".',
                'source'   => 'record',
            ];
        }

        $newIng = $resolved['ingredient'];
        $uncovered = [];
        $seen = [];
        $newKey = $this->norm($newDrugName);
        foreach ($meds as $medName) {
            $medName = trim((string) $medName);
            $key = $this->norm($medName);
            if ($medName === '' || $key === $newKey || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $medIng = $this->resolve($medName)['ingredient'];
            $hit = $this->tableInteraction($newIng, $medIng);
            if ($hit) {
                $warnings[] = [
                    'type'     => 'interaction',
                    'severity' => (string) $hit['severity'],
                    'drug'     => $newDrugName,
                    'against'  => $medName,
                    'message'  => (string) $hit['description'],
                    'source'   => 'table',
                ];
            } elseif ($newIng !== '' && $medIng !== '' && $newIng !== $medIng) {
                $uncovered[$medIng] = $medName;
            }
        }

        foreach ($this->groqAdvisory($newDrugName, $newIng, $uncovered) as $w) {
            $warnings[] = $w;
        }

        return $warnings;
    }

    /** Groq advisory for uncovered pairs (best-effort; source='ai', always "verify"). */
    private function groqAdvisory(string $newName, string $newIng, array $uncoveredByIng): array
    {
        $key = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');
        if (!$key || trim((string) $key) === '' || empty($uncoveredByIng)) {
            return [];
        }
        $ings = array_slice(array_keys($uncoveredByIng), 0, 12);
        $subject = $newIng !== '' ? $newIng : $newName;

        $sys = 'You are a clinical pharmacology checker. Given a NEW drug and the patient\'s CURRENT drugs (generic names), return ONLY clinically significant drug-drug interactions as strict JSON: {"interactions":[{"with":"<current generic>","severity":"contraindicated|major|moderate|minor","note":"<short reason>"}]}. Include only real, well-established interactions; if none, return {"interactions":[]}. No prose.';
        $user = 'NEW: ' . $subject . "\nCURRENT: " . implode(', ', $ings);

        try {
            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $key],
                CURLOPT_POSTFIELDS     => json_encode([
                    'model'           => 'llama-3.3-70b-versatile',
                    'messages'        => [['role' => 'system', 'content' => $sys], ['role' => 'user', 'content' => $user]],
                    'temperature'     => 0,
                    'max_tokens'      => 600,
                    'response_format' => ['type' => 'json_object'],
                ], JSON_UNESCAPED_UNICODE),
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_CONNECTTIMEOUT => 8,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code !== 200) {
                return [];
            }
            $content = json_decode((string) $resp, true)['choices'][0]['message']['content'] ?? '';
            $items = json_decode((string) $content, true)['interactions'] ?? [];
            $allowed = ['contraindicated', 'major', 'moderate', 'minor'];
            $out = [];
            foreach ((array) $items as $it) {
                $with = trim((string) ($it['with'] ?? ''));
                $note = trim((string) ($it['note'] ?? ''));
                $sev  = strtolower(trim((string) ($it['severity'] ?? 'moderate')));
                if ($with === '' || $note === '') {
                    continue;
                }
                $out[] = [
                    'type'     => 'interaction',
                    'severity' => in_array($sev, $allowed, true) ? $sev : 'moderate',
                    'drug'     => $newName,
                    'against'  => $with,
                    'message'  => $note,
                    'source'   => 'ai',
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
