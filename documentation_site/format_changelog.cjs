const fs = require('fs');
const path = require('path');

const files = [
    '/var/www/html/clinic_2/documentation_site/src/locales/en.json',
    '/var/www/html/clinic_2/documentation_site/src/locales/ar.json'
];

function processContent(content) {
    const lines = content.split('\n');
    return lines.map((line, index) => {
        let trimmed = line.trim();
        if (!trimmed) return line;

        // 1. Remove prefixes (case-insensitive)
        const prefixes = ['feat:', 'chore:', 'docs:', 'refactor:', 'style:', 'fix:', 'V 7.', 'V 6.'];
        // Note: 'V 7.' etc might be part of a title line, but the user said "replace feat and chore", 
        // and "add - before each line... unless it's a title".
        // Let's be careful. The user specifically asked to replace feat/chore.
        // And add bullets.

        // Detailed logic based on user request:
        // "replace it also in the arabic translation file feat and chore"
        // "make sure to add - before each line... unless it's a title"

        // Regex for prefixes
        trimmed = trimmed.replace(/^(feat|chore|docs|refactor|style|fix):\s*/i, '');

        // 2. Check if it's a title
        const isTitle =
            trimmed.endsWith(':') ||
            /^(Version|Volume|V |الإصدار)\s/.test(trimmed) ||
            // Heuristic for the "Final Release" or "Update" headers which might not end in colon
            /^(الإصدار|Version)\s.*\s(-|–)\s/.test(trimmed);

        if (isTitle) {
            return trimmed;
        }

        // 3. Add bullet point if not present
        if (!trimmed.startsWith('-')) {
            return `- ${trimmed}`;
        }

        return trimmed;
    }).join('\n');
}

files.forEach(filePath => {
    try {
        const data = fs.readFileSync(filePath, 'utf8');
        const json = JSON.parse(data);

        // Traverse to find changelog entries
        if (json.sections && json.sections.changelog) {
            const changelog = json.sections.changelog;
            for (const key in changelog) {
                // Skip hero, notice, overview, github keys, process only version keys (v*)
                // Actually, looking at the file, the version keys are like "v7_1_3", "v7_0" etc.
                // But wait, the user might want this applied to the 'content' field of these objects.

                if (key.startsWith('v')) {
                    if (changelog[key].content) {
                        changelog[key].content = processContent(changelog[key].content);
                    }
                }
            }
        } else {
            console.error(`Changelog section not found in ${filePath}`);
        }

        fs.writeFileSync(filePath, JSON.stringify(json, null, 4), 'utf8');
        console.log(`Processed ${filePath}`);

    } catch (err) {
        console.error(`Error processing ${filePath}:`, err);
    }
});
