#!/bin/bash

################################################################################
# Database Migration & Optimization Script
# Phase 12.2 - Production Database Migration
################################################################################

set -e

# Configuration
APP_DIR="/var/www/shelve"
BACKUP_DIR="/var/backups/shelve"
LOG_FILE="/var/log/shelve-migration.log"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
MYSQL_USER="${DB_USERNAME:-shelve}"
MYSQL_DB="${DB_DATABASE:-shelve}"
MYSQL_HOST="${DB_HOST:-localhost}"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Logging functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

################################################################################
# Function: Create backup directory
################################################################################
create_backup_dir() {
    log "Creating backup directory..."
    mkdir -p "$BACKUP_DIR"
    chmod 750 "$BACKUP_DIR"
}

################################################################################
# Function: Backup database
################################################################################
backup_database() {
    log "Starting database backup..."

    local backup_file="$BACKUP_DIR/shelve_db_${TIMESTAMP}.sql"
    local backup_compressed="$backup_file.gz"

    info "Backup file: $backup_compressed"

    # Create backup with mysqldump
    mysqldump \
        --user="$MYSQL_USER" \
        --host="$MYSQL_HOST" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --compress \
        "$MYSQL_DB" > "$backup_file"

    # Compress backup
    gzip -9 "$backup_file"

    # Set permissions
    chmod 640 "$backup_compressed"

    # Verify backup
    if [ -f "$backup_compressed" ]; then
        local size=$(du -h "$backup_compressed" | cut -f1)
        log "✓ Backup created successfully: $backup_compressed ($size)"
        echo "$backup_compressed" > "$BACKUP_DIR/latest_backup.txt"
    else
        error "Backup failed!"
        exit 1
    fi
}

################################################################################
# Function: Test database connection
################################################################################
test_connection() {
    log "Testing database connection..."

    if mysql --user="$MYSQL_USER" --host="$MYSQL_HOST" -e "USE $MYSQL_DB; SELECT 1;" > /dev/null 2>&1; then
        log "✓ Database connection successful"
    else
        error "Cannot connect to database!"
        exit 1
    fi
}

################################################################################
# Function: Run migrations
################################################################################
run_migrations() {
    log "Running database migrations..."

    cd "$APP_DIR"

    # Check pending migrations
    local pending=$(php artisan migrate:status --pending | grep -c "Pending" || echo "0")

    if [ "$pending" -gt 0 ]; then
        info "$pending migration(s) pending"

        # Run migrations
        php artisan migrate --force --step

        if [ $? -eq 0 ]; then
            log "✓ Migrations completed successfully"
        else
            error "Migration failed!"
            return 1
        fi
    else
        log "✓ No pending migrations"
    fi
}

################################################################################
# Function: Optimize database indexes
################################################################################
optimize_indexes() {
    log "Optimizing database indexes..."

    cd "$APP_DIR"

    # Create optimization SQL
    mysql --user="$MYSQL_USER" --host="$MYSQL_HOST" "$MYSQL_DB" <<'EOF'
-- Analyze tables
ANALYZE TABLE attachments;
ANALYZE TABLE record_physicals;
ANALYZE TABLE digital_folders;
ANALYZE TABLE digital_documents;
ANALYZE TABLE artifacts;
ANALYZE TABLE books;
ANALYZE TABLE publishers;
ANALYZE TABLE authors;
ANALYZE TABLE subjects;
ANALYZE TABLE periodicals;
ANALYZE TABLE issues;

-- Optimize tables
OPTIMIZE TABLE attachments;
OPTIMIZE TABLE record_physicals;
OPTIMIZE TABLE digital_folders;
OPTIMIZE TABLE digital_documents;
OPTIMIZE TABLE artifacts;
OPTIMIZE TABLE books;
OPTIMIZE TABLE publishers;
OPTIMIZE TABLE authors;
OPTIMIZE TABLE subjects;
OPTIMIZE TABLE periodicals;
OPTIMIZE TABLE issues;

-- Show index statistics
SELECT
    TABLE_NAME,
    INDEX_NAME,
    CARDINALITY,
    ROUND(CARDINALITY / (SELECT COUNT(*) FROM attachments), 2) AS selectivity
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('attachments', 'books', 'digital_documents')
ORDER BY TABLE_NAME, INDEX_NAME;
EOF

    log "✓ Database indexes optimized"
}

################################################################################
# Function: Configure replication (if applicable)
################################################################################
configure_replication() {
    log "Checking replication configuration..."

    # Check if replication is configured
    local is_replica=$(mysql --user="$MYSQL_USER" --host="$MYSQL_HOST" -e "SHOW SLAVE STATUS\G" | grep -c "Master_Host" || echo "0")

    if [ "$is_replica" -gt 0 ]; then
        warning "Server is configured as replica"

        # Show replication status
        mysql --user="$MYSQL_USER" --host="$MYSQL_HOST" -e "SHOW SLAVE STATUS\G" | grep -E "Slave_IO_Running|Slave_SQL_Running|Seconds_Behind_Master"
    else
        info "No replication configured (standalone server)"
    fi
}

################################################################################
# Function: Validate data integrity
################################################################################
validate_data() {
    log "Validating data integrity..."

    cd "$APP_DIR"

    # Count records in main tables
    mysql --user="$MYSQL_USER" --host="$MYSQL_HOST" "$MYSQL_DB" <<'EOF'
SELECT 'attachments' AS table_name, COUNT(*) AS count FROM attachments
UNION ALL
SELECT 'record_physicals', COUNT(*) FROM record_physicals
UNION ALL
SELECT 'digital_folders', COUNT(*) FROM digital_folders
UNION ALL
SELECT 'digital_documents', COUNT(*) FROM digital_documents
UNION ALL
SELECT 'artifacts', COUNT(*) FROM artifacts
UNION ALL
SELECT 'books', COUNT(*) FROM books
UNION ALL
SELECT 'periodicals', COUNT(*) FROM periodicals
UNION ALL
SELECT 'publishers', COUNT(*) FROM publishers
UNION ALL
SELECT 'authors', COUNT(*) FROM authors
UNION ALL
SELECT 'subjects', COUNT(*) FROM subjects;
EOF

    # Check for orphaned records
    info "Checking for orphaned records..."

    local orphans=$(mysql --user="$MYSQL_USER" --host="$MYSQL_HOST" "$MYSQL_DB" <<'EOF' | grep -v "orphaned_count" | awk '{sum+=$1} END {print sum}'
SELECT COUNT(*) AS orphaned_count FROM attachments a
LEFT JOIN record_physicals rp ON a.attachable_id = rp.id AND a.attachable_type = 'App\\Models\\RecordPhysical'
WHERE a.attachable_type = 'App\\Models\\RecordPhysical' AND rp.id IS NULL;
EOF
)

    if [ "$orphans" -gt 0 ]; then
        warning "Found $orphans orphaned attachment(s)"
    else
        log "✓ No orphaned records found"
    fi

    log "✓ Data integrity validation complete"
}

################################################################################
# Function: Generate migration report
################################################################################
generate_report() {
    log "Generating migration report..."

    local report_file="$BACKUP_DIR/migration_report_${TIMESTAMP}.txt"

    cat > "$report_file" <<EOF
================================================================================
SHELVE DATABASE MIGRATION REPORT
================================================================================
Date: $(date)
Database: $MYSQL_DB
Host: $MYSQL_HOST

BACKUP INFORMATION
--------------------------------------------------------------------------------
Backup File: $(cat "$BACKUP_DIR/latest_backup.txt" 2>/dev/null || echo "N/A")
Backup Size: $(du -h "$(cat "$BACKUP_DIR/latest_backup.txt" 2>/dev/null)" 2>/dev/null | cut -f1 || echo "N/A")

MIGRATION STATUS
--------------------------------------------------------------------------------
$(cd "$APP_DIR" && php artisan migrate:status)

DATABASE STATISTICS
--------------------------------------------------------------------------------
$(mysql --user="$MYSQL_USER" --host="$MYSQL_HOST" "$MYSQL_DB" <<'EOSQL'
SELECT
    TABLE_NAME,
    TABLE_ROWS,
    ROUND(DATA_LENGTH / 1024 / 1024, 2) AS 'Data (MB)',
    ROUND(INDEX_LENGTH / 1024 / 1024, 2) AS 'Index (MB)',
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'Total (MB)'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_TYPE = 'BASE TABLE'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
LIMIT 20;
EOSQL
)

================================================================================
Report generated: $report_file
================================================================================
EOF

    log "✓ Report generated: $report_file"
    cat "$report_file"
}

################################################################################
# Main execution
################################################################################
main() {
    log "========================================="
    log "SHELVE Database Migration Script"
    log "Phase 12.2 - Production Deployment"
    log "========================================="

    # Check if running as root or with sudo
    if [ "$EUID" -ne 0 ]; then
        warning "Not running as root. Some operations may fail."
    fi

    # Create backup directory
    create_backup_dir

    # Test connection
    test_connection

    # Create backup
    backup_database

    # Run migrations
    run_migrations

    # Optimize indexes
    optimize_indexes

    # Check replication
    configure_replication

    # Validate data
    validate_data

    # Generate report
    generate_report

    log "========================================="
    log "✓ Database migration completed successfully!"
    log "========================================="
}

# Run main function
main "$@"
