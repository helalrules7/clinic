#!/bin/bash
# Continuous sync loop - pulls from remote periodically

echo "=== Clinic Database Sync Service ==="
echo "Sync interval: ${SYNC_INTERVAL:-300} seconds"

# Wait for databases to be ready
sleep 10

# Initial sync - pull everything from remote
echo "Performing initial sync from remote..."
/scripts/pull-from-remote.sh

# Continuous sync loop
while true; do
    echo "[$(date)] Starting sync cycle..."

    # Pull changes from remote
    /scripts/pull-from-remote.sh

    # Push local changes to remote (if enabled)
    if [ "${ENABLE_PUSH:-false}" = "true" ]; then
        /scripts/push-to-remote.sh
    fi

    echo "[$(date)] Sync complete. Sleeping for ${SYNC_INTERVAL:-300} seconds..."
    sleep ${SYNC_INTERVAL:-300}
done
