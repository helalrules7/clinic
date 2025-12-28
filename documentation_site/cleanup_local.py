
import os

def clean_local_images():
    count = 0
    deleted_size = 0
    
    for root, dirs, files in os.walk('public/assets/images'):
        for file in files:
            # We are looking for original images that have an optimized counterpart
            if file.lower().endswith(('.png', '.jpg', '.jpeg')) and not file.endswith('-opt.png'):
                base, ext = os.path.splitext(file)
                optimized_version = f"{base}-opt.png"
                
                # Check if optimized version exists in the same directory
                if os.path.exists(os.path.join(root, optimized_version)):
                    full_path = os.path.join(root, file)
                    try:
                        size = os.path.getsize(full_path)
                        os.remove(full_path)
                        print(f"Deleted: {full_path}")
                        count += 1
                        deleted_size += size
                    except Exception as e:
                        print(f"Error deleting {full_path}: {e}")

    print(f"Cleanup complete. Deleted {count} files. Frees {deleted_size / 1024 / 1024:.2f} MB.")

if __name__ == "__main__":
    clean_local_images()
