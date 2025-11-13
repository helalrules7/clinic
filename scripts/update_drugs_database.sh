#!/bin/bash
#
# Shell script to update drugs database
# This script calls the PHP CLI script to update the drugs database
#
# Usage: ./update_drugs_database.sh
#

# Set script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# PHP script path
PHP_SCRIPT="$SCRIPT_DIR/update_drugs_database.php"

# Log directory
LOG_DIR="$PROJECT_ROOT/logs"
mkdir -p "$LOG_DIR"

# Log file
LOG_FILE="$LOG_DIR/drugs_update_$(date +%Y-%m-%d).log"

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Check if PHP is available
if ! command -v php &> /dev/null; then
    log_message "ERROR: PHP is not installed or not in PATH"
    exit 1
fi

# Check if PHP script exists
if [ ! -f "$PHP_SCRIPT" ]; then
    log_message "ERROR: PHP script not found: $PHP_SCRIPT"
    exit 1
fi

# Make PHP script executable
chmod +x "$PHP_SCRIPT"

log_message "Starting drugs database update..."
log_message "PHP version: $(php -v | head -n 1)"
log_message "PHP script: $PHP_SCRIPT"
log_message "Project root: $PROJECT_ROOT"

# Run PHP script
php "$PHP_SCRIPT" 2>&1 | tee -a "$LOG_FILE"

# Get exit code
EXIT_CODE=${PIPESTATUS[0]}

if [ $EXIT_CODE -eq 0 ]; then
    log_message "Drugs database update completed successfully"
    exit 0
else
    log_message "ERROR: Drugs database update failed with exit code: $EXIT_CODE"
    exit $EXIT_CODE
fi

