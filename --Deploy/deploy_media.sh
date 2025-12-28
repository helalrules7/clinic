#!/bin/bash

# Media Gallery Deployment Script
# Deploys media gallery files to remote server

REMOTE_HOST="217.76.57.212"
REMOTE_USER="root"
REMOTE_PASS="Carmen1230@@"
REMOTE_PATH="/home/hclinic/web/roaya.hclinic.clinic/public_html"

echo "🚀 Starting Media Gallery Deployment..."

# Files to deploy
FILES=(
    "app/Controllers/MediaController.php"
    "app/Views/media/index.php"
    "app/Views/layouts/main.php"
    "index.php"
)

# Create remote directories
echo "📁 Creating remote directories..."
sshpass -p "$REMOTE_PASS" ssh -o StrictHostKeyChecking=no "$REMOTE_USER@$REMOTE_HOST" "mkdir -p $REMOTE_PATH/app/Controllers $REMOTE_PATH/app/Views/media $REMOTE_PATH/app/Views/layouts"

# Deploy files
echo "📤 Deploying files..."
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  → Uploading $file..."
        sshpass -p "$REMOTE_PASS" scp -o StrictHostKeyChecking=no "$file" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/$file"
        if [ $? -eq 0 ]; then
            echo "    ✅ $file uploaded successfully"
        else
            echo "    ❌ Failed to upload $file"
        fi
    else
        echo "    ⚠️  File not found: $file"
    fi
done

# Set permissions
echo "🔐 Setting permissions..."
sshpass -p "$REMOTE_PASS" ssh -o StrictHostKeyChecking=no "$REMOTE_USER@$REMOTE_HOST" "chmod -R 755 $REMOTE_PATH/app/Controllers/MediaController.php $REMOTE_PATH/app/Views/media $REMOTE_PATH/app/Views/layouts/main.php"

# Restart PHP-FPM
echo "🔄 Restarting PHP-FPM..."
sshpass -p "$REMOTE_PASS" ssh -o StrictHostKeyChecking=no "$REMOTE_USER@$REMOTE_HOST" "systemctl restart php8.2-fpm && echo '✅ PHP restarted'"

echo "✅ Deployment completed!"
echo ""
echo "📋 Summary:"
echo "  - MediaController.php → $REMOTE_PATH/app/Controllers/"
echo "  - media/index.php → $REMOTE_PATH/app/Views/media/"
echo "  - layouts/main.php → $REMOTE_PATH/app/Views/layouts/"
echo "  - index.php → $REMOTE_PATH/"
echo ""
echo "🌐 Access the Media Gallery at: https://roaya.hclinic.clinic/doctor/media"

