# Documentation Site Deployment Guide

## Remote Server Information
- **IP Address:** `45.93.138.184`
- **SSH User:** `root`
- **SSH Key Path:** `/var/www/html/clinic_2/keys/OpenSSHPrivate`
- **Remote Path:** `/home/AhmedHelal/web/hclinic.clinic/public_html/docs/opth`
- **Remote Owner:** `AhmedHelal:www-data`

## Deployment Command
Run the following from the `documentation_site` directory:

### 1. Optimization & Cleanup
```bash
# Optimize images
python3 optimize_images.py

# Update code references to use optimized images
python3 replace_new_refs.py

# Clean unused local unoptimized images
python3 cleanup_local.py
```

### 2. Build & Deploy
```bash
# Build
npm run build

# Deploy (Rsync) with --delete to remove unused files on remote
rsync -avz --delete -e "ssh -i /var/www/html/clinic_2/keys/OpenSSHPrivate -o StrictHostKeyChecking=no" --exclude 'node_modules' --exclude '.git' --exclude '.env' dist/ root@45.93.138.184:/home/AhmedHelal/web/hclinic.clinic/public_html/docs/opth/

# Fix Permissions
ssh -i /var/www/html/clinic_2/keys/OpenSSHPrivate -o StrictHostKeyChecking=no root@45.93.138.184 "chown -R AhmedHelal:www-data /home/AhmedHelal/web/hclinic.clinic/public_html/docs/opth"
```
