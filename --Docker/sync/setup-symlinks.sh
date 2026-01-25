#!/bin/sh
# Setup symlinks and fix .htaccess for proper file access in Docker container
# Run this after container starts

echo "[SETUP] Creating symlinks for file access..."

# 1. public/public -> . (handles /public/... URLs)
cd /var/www/html/public
ln -sf . public 2>/dev/null || true

# 2. public/storage -> ../storage (handles /storage/... URLs)
ln -sf ../storage storage 2>/dev/null || true

# 3. public/uploads/patients -> /var/www/html/uploads/patients
rm -rf /var/www/html/public/uploads/patients 2>/dev/null
ln -sf /var/www/html/uploads/patients /var/www/html/public/uploads/patients 2>/dev/null || true

# 4. storage/uploads -> ../app/storage/uploads
cd /var/www/html/storage
ln -sf ../app/storage/uploads uploads 2>/dev/null || true

echo "[SETUP] Symlinks created successfully"

# 5. Fix .htaccess for storage/uploads (allows image access)
echo "[SETUP] Fixing .htaccess for storage/uploads..."
cat > /var/www/html/app/storage/uploads/.htaccess << 'HTACCESS'
# Allow access to specific file types only
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>

<FilesMatch "\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt|webp|svg)$">
    <IfModule mod_authz_core.c>
        Require all granted
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Allow from all
    </IfModule>
</FilesMatch>

<FilesMatch "\.php$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
</FilesMatch>
HTACCESS

echo "[SETUP] Verifying..."
ls -la /var/www/html/public/public
ls -la /var/www/html/public/storage
ls -la /var/www/html/public/uploads/patients 2>/dev/null | head -1
ls -la /var/www/html/storage/uploads 2>/dev/null | head -1
echo "[SETUP] Setup complete!"
