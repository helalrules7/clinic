#!/bin/sh
# File sync loop - runs in Alpine container to sync attachments from remote

echo "=== Clinic File Sync Service ==="
echo "Remote: $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH"
echo "Sync interval: ${FILE_SYNC_INTERVAL:-600} seconds"

# Install required packages
echo "Installing rsync and sshpass..."
apk add --no-cache rsync sshpass openssh-client > /dev/null 2>&1

# Wait a bit for other services to start
sleep 5

# Initial sync
echo "Performing initial file sync..."
/scripts/file-sync-pull.sh

# Continuous sync loop
while true; do
    echo "[$(date)] Sleeping for ${FILE_SYNC_INTERVAL:-600} seconds..."
    sleep ${FILE_SYNC_INTERVAL:-600}

    echo "[$(date)] Starting file sync cycle..."
    /scripts/file-sync-pull.sh
done
