#!/bin/bash

################################################################################
# Backup & Disaster Recovery Script
# Phase 12.7 - Automated Backup System
################################################################################

set -e

# Configuration
APP_DIR="/var/www/shelve"
BACKUP_DIR="/var/backups/shelve"
RETENTION_DAYS=30
LOG_FILE="/var/log/shelve-backup.log"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date +'%H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"; }
error() { echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"; }

################################################################################
# Backup Database
################################################################################
backup_database() {
    log "Backing up database..."

    local backup_file="$BACKUP_DIR/db/shelve_db_${TIMESTAMP}.sql.gz"
    mkdir -p "$BACKUP_DIR/db"

    mysqldump \
        --user="${DB_USERNAME}" \
        --password="${DB_PASSWORD}" \
        --host="${DB_HOST}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        "${DB_DATABASE}" | gzip -9 > "$backup_file"

    chmod 640 "$backup_file"
    log "✓ Database backup: $backup_file ($(du -h "$backup_file" | cut -f1))"
}

################################################################################
# Backup Files
################################################################################
backup_files() {
    log "Backing up application files..."

    local backup_file="$BACKUP_DIR/files/shelve_files_${TIMESTAMP}.tar.gz"
    mkdir -p "$BACKUP_DIR/files"

    cd "$APP_DIR"
    tar -czf "$backup_file" \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        .env \
        storage/app \
        public/uploads

    chmod 640 "$backup_file"
    log "✓ Files backup: $backup_file ($(du -h "$backup_file" | cut -f1))"
}

################################################################################
# Rotate old backups
################################################################################
rotate_backups() {
    log "Rotating old backups (retention: $RETENTION_DAYS days)..."

    find "$BACKUP_DIR/db" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
    find "$BACKUP_DIR/files" -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

    log "✓ Old backups removed"
}

################################################################################
# Restore Database
################################################################################
restore_database() {
    local backup_file="$1"

    if [ -z "$backup_file" ]; then
        error "No backup file specified"
        return 1
    fi

    log "Restoring database from: $backup_file"

    gunzip < "$backup_file" | mysql \
        --user="${DB_USERNAME}" \
        --password="${DB_PASSWORD}" \
        --host="${DB_HOST}" \
        "${DB_DATABASE}"

    log "✓ Database restored"
}

################################################################################
# Main
################################################################################
case "${1:-backup}" in
    backup)
        log "Starting full backup..."
        backup_database
        backup_files
        rotate_backups
        log "✓ Backup completed"
        ;;
    restore)
        restore_database "$2"
        ;;
    *)
        echo "Usage: $0 {backup|restore <file>}"
        exit 1
        ;;
esac
