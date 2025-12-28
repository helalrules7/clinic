import os

replacements = []
with open('replacements.log', 'r') as f:
    for line in f:
        parts = line.strip().split('|')
        if len(parts) == 2:
            # We want to replace "foo.png" with "foo-opt.png"
            # The paths in log are like /assets/images/...
            # In code they might be relative or full. 
            # Safest is to replace the filename itself if unique, or the end of path.
            # But wait, 009.png -> 009-opt.png is safe.
            # 005-v2.png -> 005-v2-opt.png is safe.
            orig = parts[0].strip()
            new = parts[1].strip()
            replacements.append((orig, new))

# Extensions to scan
exts = ('.tsx', '.ts', '.css', '.json') # json for search.ts content maybe?

count = 0
for root, dirs, files in os.walk('src'):
    for file in files:
        if file.endswith(exts):
            path = os.path.join(root, file)
            with open(path, 'r') as f:
                content = f.read()
            
            new_content = content
            for orig, new in replacements:
                # We try to match the exact string from the logs which starts with /assets
                # But code might use "assets/..." or "/docs/opth/assets..."
                # Let's try to match the filename part mostly?
                # Actually, the code uses "/docs/opth/assets/..." mostly.
                # So we should prepend /docs/opth to our replacement keys for safety in .tsx
                
                # Case 1: /docs/opth prefix
                orig_docs = "/docs/opth" + orig
                new_docs = "/docs/opth" + new
                new_content = new_content.replace(orig_docs, new_docs)
                
                # Case 2: just assets/ prefix (if any)
                # Be careful not to replace already replaced ones
                
            if new_content != content:
                with open(path, 'w') as f:
                    f.write(new_content)
                print(f"Updated {path}")
                count += 1

print(f"Updated {count} files.")
