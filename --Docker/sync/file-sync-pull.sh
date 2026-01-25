#!/bin/sh
# Pull all uploads from remote server

echo "[FILE-SYNC] Starting file sync from remote..."

# Rsync options (exclude .htaccess to preserve local fixes)
RSYNC_OPTS="-avz --partial --delete-after --exclude='.htaccess'"
SSH_OPTS="-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=ERROR"

# 1. Sync app/storage (includes uploads, logs, cache)
echo "[FILE-SYNC] [1/4] Syncing app/storage..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/app/storage/" \
    "/app/storage/" 2>/dev/null && \
    echo "[FILE-SYNC] app/storage synced successfully" || \
    echo "[FILE-SYNC] Warning: Could not sync app/storage"

# 2. Sync storage/uploads (attachments, forum files)
echo "[FILE-SYNC] [2/4] Syncing storage/uploads..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/storage/uploads/" \
    "/app/storage/uploads/" 2>/dev/null && \
    echo "[FILE-SYNC] storage/uploads synced successfully" || \
    echo "[FILE-SYNC] Warning: Could not sync storage/uploads"

# 3. Sync public/uploads (user profiles, logos, patient images)
echo "[FILE-SYNC] [3/4] Syncing public/uploads..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/public/uploads/" \
    "/public/uploads/" 2>/dev/null && \
    echo "[FILE-SYNC] public/uploads synced successfully" || \
    echo "[FILE-SYNC] Warning: Could not sync public/uploads"

# 4. Sync root /uploads (patients, attachments)
echo "[FILE-SYNC] [4/4] Syncing /uploads..."
sshpass -p "$REMOTE_PASS" rsync $RSYNC_OPTS \
    -e "ssh $SSH_OPTS" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/uploads/" \
    "/uploads/" 2>/dev/null && \
    echo "[FILE-SYNC] /uploads synced successfully" || \
    echo "[FILE-SYNC] Warning: Could not sync /uploads"

echo ""
echo "[FILE-SYNC] ===== Sync Summary ====="
echo "[FILE-SYNC] Completed at $(date)"
echo "[FILE-SYNC] app/storage files: $(find /app/storage -type f 2>/dev/null | wc -l)"
echo "[FILE-SYNC] public/uploads files: $(find /public/uploads -type f 2>/dev/null | wc -l)"
echo "[FILE-SYNC] /uploads files: $(find /uploads -type f 2>/dev/null | wc -l)"
