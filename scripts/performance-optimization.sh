#!/bin/bash

################################################################################
# Performance Optimization Script
# Phase 12.5 - Production Performance Tuning
################################################################################

set -e

APP_DIR="/var/www/shelve"
LOG_FILE="/var/log/shelve-performance.log"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date +'%H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"; }

################################################################################
# Configure OPcache
################################################################################
configure_opcache() {
    log "Configuring OPcache..."

    cat > /etc/php/8.2/fpm/conf.d/99-opcache.ini <<'EOF'
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=0
opcache.validate_timestamps=0
EOF

    systemctl restart php8.2-fpm
    log "✓ OPcache configured"
}

################################################################################
# Configure Redis
################################################################################
configure_redis() {
    log "Configuring Redis..."

    cd "$APP_DIR"

    # Update cache config
    cat >> config/cache.php <<'EOF'
    'redis' => [
        'client' => 'phpredis',
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
EOF

    log "✓ Redis configured"
}

################################################################################
# Optimize Laravel
################################################################################
optimize_laravel() {
    log "Optimizing Laravel..."

    cd "$APP_DIR"

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    log "✓ Laravel optimized"
}

################################################################################
# Optimize Database
################################################################################
optimize_database() {
    log "Optimizing database queries..."

    cd "$APP_DIR"

    mysql --user="${DB_USERNAME}" --host="${DB_HOST}" "${DB_DATABASE}" <<'EOF'
-- Add indexes for performance
ALTER TABLE attachments ADD INDEX idx_attachable (attachable_type, attachable_id);
ALTER TABLE books ADD INDEX idx_publisher (publisher_id);
ALTER TABLE books ADD INDEX idx_status (status);
ALTER TABLE digital_documents ADD INDEX idx_folder (digital_folder_id);

-- Analyze tables
ANALYZE TABLE attachments, books, digital_documents, artifacts, periodicals;
EOF

    log "✓ Database optimized"
}

################################################################################
# Main
################################################################################
log "========================================="
log "SHELVE Performance Optimization"
log "========================================="

configure_opcache
configure_redis
optimize_laravel
optimize_database

log "✓ Performance optimization completed!"
