-- Drug interaction knowledge base (Feature 2 — hybrid drug-safety).
-- Curated pairs of ACTIVE INGREDIENTS (generic names, lowercase) with a severity
-- and a short clinical note. Pairs are stored with ingredient_a < ingredient_b
-- (alphabetical) so a lookup just sorts the queried pair. This is the
-- AUTHORITATIVE source; pairs NOT found here fall back to a Groq advisory check.
-- Allergy alerts use the existing medical_history (no data here).

CREATE TABLE IF NOT EXISTS drug_interactions (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ingredient_a VARCHAR(190) NOT NULL,
    ingredient_b VARCHAR(190) NOT NULL,
    severity     ENUM('contraindicated','major','moderate','minor') NOT NULL DEFAULT 'moderate',
    description  VARCHAR(500) NOT NULL,
    source       VARCHAR(40)  NOT NULL DEFAULT 'curated',
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_pair (ingredient_a, ingredient_b),
    KEY idx_a (ingredient_a),
    KEY idx_b (ingredient_b)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO drug_interactions (ingredient_a, ingredient_b, severity, description) VALUES
-- Anticoagulant / antiplatelet bleeding risk
('aspirin','warfarin','major','Greatly increased bleeding risk.'),
('ibuprofen','warfarin','major','NSAID raises bleeding risk with warfarin.'),
('diclofenac','warfarin','major','NSAID raises bleeding risk with warfarin.'),
('naproxen','warfarin','major','NSAID raises bleeding risk with warfarin.'),
('ketorolac','warfarin','contraindicated','High GI/bleeding risk — avoid together.'),
('fluconazole','warfarin','major','Potentiates warfarin — raised INR/bleeding.'),
('amiodarone','warfarin','major','Raises warfarin effect — bleeding risk.'),
('metronidazole','warfarin','major','Potentiates warfarin — bleeding risk.'),
('aspirin','ibuprofen','moderate','Ibuprofen can blunt aspirin antiplatelet effect.'),
('aspirin','ketorolac','moderate','Additive GI/bleeding risk.'),
('clopidogrel','omeprazole','moderate','Omeprazole may reduce clopidogrel activation.'),
-- NSAID / methotrexate / lithium / renal
('aspirin','methotrexate','major','NSAID/salicylate raises methotrexate toxicity.'),
('ibuprofen','methotrexate','major','NSAID raises methotrexate toxicity.'),
('diclofenac','methotrexate','major','NSAID raises methotrexate toxicity.'),
('methotrexate','trimethoprim','major','Additive antifolate — marrow suppression.'),
('ibuprofen','lithium','major','NSAID raises lithium levels.'),
('enalapril','lithium','major','ACE inhibitor raises lithium levels.'),
-- ACE inhibitor + potassium-sparing / potassium
('enalapril','spironolactone','major','Hyperkalemia risk.'),
('lisinopril','spironolactone','major','Hyperkalemia risk.'),
('potassium chloride','spironolactone','major','Hyperkalemia risk.'),
('enalapril','potassium chloride','major','Hyperkalemia risk.'),
('enalapril','ibuprofen','moderate','NSAID can reduce ACE effect / renal risk.'),
-- Statin rhabdomyolysis
('clarithromycin','simvastatin','major','Raises simvastatin — rhabdomyolysis risk.'),
('erythromycin','simvastatin','major','Raises simvastatin — rhabdomyolysis risk.'),
('amiodarone','simvastatin','major','Raises simvastatin — myopathy risk.'),
-- Serotonin syndrome
('fluoxetine','tramadol','major','Serotonin syndrome risk.'),
('sertraline','tramadol','major','Serotonin syndrome risk.'),
('linezolid','sertraline','major','Serotonin syndrome risk.'),
-- Nitrate + PDE5 (contraindicated)
('nitroglycerin','sildenafil','contraindicated','Severe hypotension — never combine.'),
('isosorbide dinitrate','sildenafil','contraindicated','Severe hypotension — never combine.'),
-- Cardiac / QT / levels
('digoxin','verapamil','major','Raises digoxin levels.'),
('amiodarone','digoxin','major','Raises digoxin levels.'),
('ciprofloxacin','theophylline','major','Raises theophylline — toxicity.'),
('ciprofloxacin','tizanidine','contraindicated','Severe hypotension/sedation — avoid.'),
('carbamazepine','clarithromycin','major','Raises carbamazepine — toxicity.'),
('allopurinol','azathioprine','major','Raises azathioprine toxicity — marrow suppression.'),
-- Ophthalmology-relevant (topical beta-blocker adds to systemic rate control)
('timolol','verapamil','moderate','Additive bradycardia / AV block.'),
('diltiazem','timolol','moderate','Additive bradycardia / AV block.');
