import os

replacements = []
if os.path.exists('new_replacements.log'):
    with open('new_replacements.log', 'r') as f:
        for line in f:
            parts = line.strip().split('|')
            if len(parts) == 2:
                replacements.append((parts[0].strip(), parts[1].strip()))

if not replacements:
    print("No replacements to make.")
    exit(0)

count = 0
for root, dirs, files in os.walk('src'):
    for file in files:
        if file.endswith(('.tsx', '.ts')):
            path = os.path.join(root, file)
            with open(path, 'r') as f:
                content = f.read()
            
            new_content = content
            for orig, new in replacements:
                # Code typically uses "/docs/opth" prefix
                orig_docs = "/docs/opth" + orig
                new_docs = "/docs/opth" + new
                new_content = new_content.replace(orig_docs, new_docs)
            
            if new_content != content:
                with open(path, 'w') as f:
                    f.write(new_content)
                print(f"Updated {path}")
                count += 1

print(f"Updated {count} files.")
