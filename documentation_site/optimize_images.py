import os
from PIL import Image

def optimize_image(path):
    # Skip if it is already an optimized file
    if path.endswith('-opt.png'):
        return None
    
    base, ext = os.path.splitext(path)
    output_path = f"{base}-opt{ext}"
    
    # Run optimization if output doesn't exist OR if source is newer than output
    if os.path.exists(output_path):
         if os.path.getmtime(path) <= os.path.getmtime(output_path):
             return None

    try:
        img = Image.open(path)
        if img.width > 1280:
            ratio = 1280 / img.width
            new_height = int(img.height * ratio)
            img = img.resize((1280, new_height), Image.Resampling.LANCZOS)
        
        img.save(output_path, optimize=True, quality=80)
        print(f"Optimized: {path} -> {output_path}")
        return (path, output_path)
    except Exception as e:
        print(f"Error optimizing {path}: {e}")
        return None

replacements = []
for root, dirs, files in os.walk('public/assets/images'):
    for file in files:
        if file.lower().endswith(('.png', '.jpg', '.jpeg')):
            full_path = os.path.join(root, file)
            res = optimize_image(full_path)
            if res:
                replacements.append(res)

if replacements:
    print(f"Optimized {len(replacements)} new images.")
    # Log for replacement script
    with open('new_replacements.log', 'w') as f:
        for orig, new in replacements:
            web_orig = orig.replace('public', '')
            web_new = new.replace('public', '')
            f.write(f"{web_orig}|{web_new}\n")
else:
    print("No new images to optimize.")
