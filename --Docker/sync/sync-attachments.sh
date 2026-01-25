#!/bin/bash
# Sync ALL uploads from remote server to local
# Run this from the host machine (not inside Docker)

set -e

# Remote server credentials
REMOTE_HOST="217.76.57.212"
REMOTE_USER="root"
REMOTE_PASS="Carmen1230@@"
REMOTE_PATH="/home/hclinic/web/roaya.hclinic.clinic/public_html"

# Local paths (relative to this script's directory)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# All upload directories
APP_STORAGE="$PROJECT_ROOT/app/storage"
STORAGE_UPLOADS="$PROJECT_ROOT/app/storage/uploads"
PUBLIC_UPLOADS="$PROJECT_ROOT/public/uploads"
ROOT_UPLOADS="$PROJECT_ROOT/uploads"

echo "=== Full Upload Sync from Remote Server ==="
echo "Remote: $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH"
echo "Local project: $PROJECT_ROOT"
echo ""

# Check if sshpass is installed
if ! command -v sshpass &> /dev/null; then
    echo "Installing sshpass..."
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install hudochenkov/sshpass/sshpass 2>/dev/null || brew install esolitos/ipa/sshpass 2>/dev/null || {
            echo "ERROR: Please install sshpass manually:"
            echo "  brew install hudochenkov/sshpass/sshpass"
            exit 1
        }
    else
        apt-get install -y sshpass 2>/dev/null || yum install -y sshpass 2>/dev/null || {
            echo "ERROR: Please install sshpass"
            exit 1
        }
    fi
fi

# Create local directories
mkdir -p "$APP_STORAGE" "$STORAGE_UPLOADS" "$PUBLIC_UPLOADS" "$ROOT_UPLOADS"

# Rsync options (exclude .htaccess to preserve local fixes)
RSYNC_OPTS="-avz --progress --partial --human-readable --exclude='.htaccess'"
SSH_OPTS="-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null"

echo "[1/4] Syncing app/storage from remote..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/app/storage/" \
    "$APP_STORAGE/" 2>/dev/null || {
    echo "Warning: Could not sync app/storage"
}

echo ""
echo "[2/4] Syncing storage/uploads from remote..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/storage/uploads/" \
    "$STORAGE_UPLOADS/" 2>/dev/null || {
    echo "Warning: Could not sync storage/uploads"
}

echo ""
echo "[3/4] Syncing public/uploads (user profiles, logos)..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/public/uploads/" \
    "$PUBLIC_UPLOADS/" 2>/dev/null || {
    echo "Warning: Could not sync public/uploads"
}

echo ""
echo "[4/4] Syncing /uploads (patients, attachments)..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/uploads/" \
    "$ROOT_UPLOADS/" 2>/dev/null || {
    echo "Warning: Could not sync /uploads"
}

echo ""
echo "=== Sync Complete ==="
echo "app/storage: $(find "$APP_STORAGE" -type f 2>/dev/null | wc -l | tr -d ' ') files"
echo "storage/uploads: $(find "$STORAGE_UPLOADS" -type f 2>/dev/null | wc -l | tr -d ' ') files"
echo "public/uploads: $(find "$PUBLIC_UPLOADS" -type f 2>/dev/null | wc -l | tr -d ' ') files"
echo "/uploads: $(find "$ROOT_UPLOADS" -type f 2>/dev/null | wc -l | tr -d ' ') files"
