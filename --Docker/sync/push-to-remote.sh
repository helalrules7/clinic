#!/bin/bash
# Push local changes to remote database
# WARNING: Use with caution - this will overwrite remote data

set -e

DUMP_DIR="/sync_data"

echo "[PUSH] Starting push to remote database..."
echo "[PUSH] WARNING: This will overwrite remote data!"

# Only push specific tables that are modified locally
# Modify this list based on your needs
TABLES_TO_PUSH="appointments patients prescriptions"

for table in $TABLES_TO_PUSH; do
    echo "[PUSH] Pushing table: $table"

    # Dump local table
    mysqldump -h "$LOCAL_DB_HOST" -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" \
        --single-transaction \
        --quick \
        --no-create-info \
        --replace \
        "$LOCAL_DB_NAME" "$table" > "$DUMP_DIR/push_${table}.sql" 2>/dev/null || {
        echo "[PUSH] Warning: Could not dump table $table"
        continue
    }

    # Push to remote
    if [ -f "$DUMP_DIR/push_${table}.sql" ] && [ -s "$DUMP_DIR/push_${table}.sql" ]; then
        mysql -h "$REMOTE_DB_HOST" -u "$REMOTE_DB_USER" -p"$REMOTE_DB_PASS" \
            "$REMOTE_DB_NAME" < "$DUMP_DIR/push_${table}.sql" && \
        echo "[PUSH] Table $table pushed successfully" || \
        echo "[PUSH] Failed to push table $table"
    fi
done

echo "[PUSH] Push completed at $(date)"
