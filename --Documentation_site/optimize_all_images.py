import os
from PIL import Image

def optimize_image(path):
    if path.endswith('-opt.png'):
        return None
    
    # Check if optimized version already exists
    base, ext = os.path.splitext(path)
    output_path = f"{base}-opt{ext}"
    
    # Special handling for existing special files to avoid double optimization if possible, 
    # but for consistency we should generate fresh opts for original files
    
    try:
        img = Image.open(path)
        # Resize if too large
        if img.width > 1280:
            ratio = 1280 / img.width
            new_height = int(img.height * ratio)
            img = img.resize((1280, new_height), Image.Resampling.LANCZOS)
        
        img.save(output_path, optimize=True, quality=80)
        
        original_size = os.path.getsize(path)
        new_size = os.path.getsize(output_path)
        print(f"Optimized {path}: {original_size} -> {new_size} bytes")
        return (path, output_path)
    except Exception as e:
        print(f"Error optimizing {path}: {e}")
        return None

images = []
for root, dirs, files in os.walk('public/assets/images'):
    for file in files:
        if file.lower().endswith(('.png', '.jpg', '.jpeg')):
            full_path = os.path.join(root, file)
            images.append(full_path)

replacements = []
for img_path in images:
    result = optimize_image(img_path)
    if result:
        replacements.append(result)

# Generate a sed script or similar for replacements
with open('replacements.log', 'w') as f:
    for orig, new in replacements:
        # We need the web path, so strip "public"
        web_orig = orig.replace('public', '')
        web_new = new.replace('public', '')
        f.write(f"{web_orig}|{web_new}\n")

print(f"Processed {len(images)} images.")
