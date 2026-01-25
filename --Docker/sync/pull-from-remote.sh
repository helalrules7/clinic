#!/bin/bash
# Pull data from remote database to local

set -e

DUMP_DIR="/sync_data"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# MySQL options to suppress password warnings
MYSQL_OPTS="--defaults-extra-file=/dev/stdin"

echo "[PULL] Starting pull from remote database..."

# Dump remote hclinic_roaya database (skip definer to avoid permission issues)
echo "[PULL] Dumping hclinic_roaya from remote..."
mysqldump -h "$REMOTE_DB_HOST" -u "$REMOTE_DB_USER" -p"$REMOTE_DB_PASS" \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --set-gtid-purged=OFF \
    --skip-definer \
    --column-statistics=0 \
    "$REMOTE_DB_NAME" 2>/dev/null > "$DUMP_DIR/roaya_remote.sql" || {
    echo "[PULL] Warning: Could not dump hclinic_roaya"
}

# Dump remote hclinic_drugs database
echo "[PULL] Dumping hclinic_drugs from remote..."
mysqldump -h "$REMOTE_DB_HOST" -u "$REMOTE_DB_USER" -p"$REMOTE_DB_PASS" \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --set-gtid-purged=OFF \
    --skip-definer \
    --column-statistics=0 \
    "$REMOTE_DRUGS_DB" 2>/dev/null > "$DUMP_DIR/drugs_remote.sql" || {
    echo "[PULL] Warning: Could not dump hclinic_drugs"
}

# Import to local database
if [ -f "$DUMP_DIR/roaya_remote.sql" ] && [ -s "$DUMP_DIR/roaya_remote.sql" ]; then
    echo "[PULL] Importing hclinic_roaya to local..."
    mysql -h "$LOCAL_DB_HOST" -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" < "$DUMP_DIR/roaya_remote.sql" 2>/dev/null
    echo "[PULL] hclinic_roaya imported successfully"
fi

if [ -f "$DUMP_DIR/drugs_remote.sql" ] && [ -s "$DUMP_DIR/drugs_remote.sql" ]; then
    echo "[PULL] Importing hclinic_drugs to local..."
    mysql -h "$LOCAL_DB_HOST" -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DRUGS_DB" < "$DUMP_DIR/drugs_remote.sql" 2>/dev/null
    echo "[PULL] hclinic_drugs imported successfully"
fi

echo "[PULL] Pull completed at $(date)"
