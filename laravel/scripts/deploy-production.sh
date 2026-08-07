#!/bin/bash

################################################################################
# Shelve Production Deployment Script
# Version: 1.0
# Date: 2025-11-07
# Description: Automated deployment script for Shelve application
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="Shelve"
APP_DIR="/var/www/shelve"
BACKUP_DIR="/var/backups/shelve"
LOG_FILE="/var/log/shelve-deployment.log"

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

confirm() {
    read -p "$1 (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        error "Deployment cancelled by user"
    fi
}

################################################################################
# Pre-deployment checks
################################################################################

pre_deployment_checks() {
    log "Starting pre-deployment checks..."

    # Check if running as root or with sudo
    if [[ $EUID -eq 0 ]]; then
        warning "This script should not be run as root. Run with sudo if needed."
    fi

    # Check if app directory exists
    if [ ! -d "$APP_DIR" ]; then
        error "Application directory $APP_DIR does not exist"
    fi

    # Check if .env exists
    if [ ! -f "$APP_DIR/.env" ]; then
        error ".env file not found in $APP_DIR"
    fi

    # Check required commands
    local required_commands=("php" "composer" "npm" "git" "mysql" "nginx")
    for cmd in "${required_commands[@]}"; do
        if ! command -v $cmd &> /dev/null; then
            error "$cmd is not installed"
        fi
    done

    # Check PHP version
    local php_version=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
    if (( $(echo "$php_version < 8.2" | bc -l) )); then
        error "PHP 8.2 or higher is required (current: $php_version)"
    fi

    log "Pre-deployment checks passed ✓"
}

################################################################################
# Backup current state
################################################################################

backup_current_state() {
    log "Creating backup of current state..."

    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local backup_path="$BACKUP_DIR/deployment_$timestamp"

    mkdir -p "$backup_path"

    # Backup database
    info "Backing up database..."
    cd "$APP_DIR"
    php artisan db:backup --path="$backup_path" 2>/dev/null || {
        # Alternative: use mysqldump
        local db_name=$(grep DB_DATABASE .env | cut -d '=' -f2)
        local db_user=$(grep DB_USERNAME .env | cut -d '=' -f2)
        local db_pass=$(grep DB_PASSWORD .env | cut -d '=' -f2)

        mysqldump -u "$db_user" -p"$db_pass" "$db_name" | gzip > "$backup_path/database.sql.gz"
    }

    # Backup storage
    info "Backing up storage..."
    tar -czf "$backup_path/storage.tar.gz" -C "$APP_DIR" storage

    # Backup .env
    info "Backing up .env..."
    cp "$APP_DIR/.env" "$backup_path/.env"

    # Save current git commit
    cd "$APP_DIR"
    git rev-parse HEAD > "$backup_path/git_commit.txt"

    log "Backup created at $backup_path ✓"
    echo "$backup_path" > /tmp/shelve_last_backup
}

################################################################################
# Enable maintenance mode
################################################################################

enable_maintenance_mode() {
    log "Enabling maintenance mode..."
    cd "$APP_DIR"
    php artisan down --refresh=15 --retry=60 --secret="$(openssl rand -hex 16)"
    log "Maintenance mode enabled ✓"
}

################################################################################
# Disable maintenance mode
################################################################################

disable_maintenance_mode() {
    log "Disabling maintenance mode..."
    cd "$APP_DIR"
    php artisan up
    log "Maintenance mode disabled ✓"
}

################################################################################
# Pull latest code
################################################################################

pull_latest_code() {
    log "Pulling latest code from repository..."

    cd "$APP_DIR"

    # Stash any local changes
    git stash

    # Fetch latest
    git fetch origin

    # Pull main branch
    git pull origin main

    log "Code updated ✓"
}

################################################################################
# Install dependencies
################################################################################

install_dependencies() {
    log "Installing dependencies..."

    cd "$APP_DIR"

    # Composer
    info "Installing PHP dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction

    # NPM
    info "Installing Node dependencies..."
    npm ci --production=false

    log "Dependencies installed ✓"
}

################################################################################
# Build assets
################################################################################

build_assets() {
    log "Building frontend assets..."

    cd "$APP_DIR"
    npm run build

    log "Assets built ✓"
}

################################################################################
# Run migrations
################################################################################

run_migrations() {
    log "Running database migrations..."

    cd "$APP_DIR"

    # Check for pending migrations
    local pending=$(php artisan migrate:status | grep -c "Pending" || true)

    if [ "$pending" -gt 0 ]; then
        info "$pending pending migration(s) found"
        confirm "Do you want to run migrations?"

        php artisan migrate --force
        log "Migrations completed ✓"
    else
        info "No pending migrations"
    fi
}

################################################################################
# Clear and optimize caches
################################################################################

optimize_application() {
    log "Optimizing application..."

    cd "$APP_DIR"

    # Clear all caches
    info "Clearing caches..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan event:clear

    # Recreate optimized caches
    info "Creating optimized caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    # Optimize autoloader
    composer dump-autoload --optimize --no-interaction

    log "Application optimized ✓"
}

################################################################################
# Set permissions
################################################################################

set_permissions() {
    log "Setting correct permissions..."

    cd "$APP_DIR"

    # Set ownership
    sudo chown -R $USER:www-data "$APP_DIR"

    # Set file permissions
    sudo find "$APP_DIR" -type f -exec chmod 644 {} \;
    sudo find "$APP_DIR" -type d -exec chmod 755 {} \;

    # Set special permissions for storage and cache
    sudo chgrp -R www-data storage bootstrap/cache
    sudo chmod -R ug+rwx storage bootstrap/cache

    log "Permissions set ✓"
}

################################################################################
# Restart services
################################################################################

restart_services() {
    log "Restarting services..."

    # PHP-FPM
    info "Restarting PHP-FPM..."
    sudo systemctl restart php8.2-fpm

    # Nginx
    info "Restarting Nginx..."
    sudo nginx -t && sudo systemctl reload nginx

    # Queue workers (Supervisor)
    info "Restarting queue workers..."
    sudo supervisorctl restart shelve-worker:*

    # Redis (optional)
    # sudo systemctl restart redis-server

    log "Services restarted ✓"
}

################################################################################
# Run tests
################################################################################

run_tests() {
    log "Running tests..."

    cd "$APP_DIR"

    # Check if we should run tests
    if [ -f "phpunit.xml" ]; then
        info "Running PHPUnit tests..."
        vendor/bin/phpunit --testsuite=Feature --stop-on-failure || warning "Some tests failed"
    else
        warning "No phpunit.xml found, skipping tests"
    fi

    log "Tests completed"
}

################################################################################
# Health check
################################################################################

health_check() {
    log "Performing health check..."

    local app_url=$(grep APP_URL "$APP_DIR/.env" | cut -d '=' -f2)

    # Check if application is responding
    info "Checking application response..."
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" "$app_url" || echo "000")

    if [ "$http_code" = "200" ] || [ "$http_code" = "302" ]; then
        log "Application is responding (HTTP $http_code) ✓"
    else
        warning "Application returned HTTP $http_code"
    fi

    # Check database connection
    info "Checking database connection..."
    cd "$APP_DIR"
    php artisan db:show 2>/dev/null && log "Database connected ✓" || warning "Database connection issue"

    # Check Redis connection
    info "Checking Redis connection..."
    php artisan tinker --execute="Redis::ping();" 2>/dev/null && log "Redis connected ✓" || warning "Redis connection issue"

    # Check queue workers
    info "Checking queue workers..."
    local workers=$(sudo supervisorctl status | grep shelve-worker | grep RUNNING | wc -l)
    log "Queue workers running: $workers ✓"
}

################################################################################
# Rollback function
################################################################################

rollback() {
    error "Deployment failed! Starting rollback..."

    if [ -f /tmp/shelve_last_backup ]; then
        local backup_path=$(cat /tmp/shelve_last_backup)

        log "Restoring from backup: $backup_path"

        # Restore database
        info "Restoring database..."
        cd "$APP_DIR"
        gunzip < "$backup_path/database.sql.gz" | mysql -u $(grep DB_USERNAME .env | cut -d '=' -f2) \
            -p$(grep DB_PASSWORD .env | cut -d '=' -f2) $(grep DB_DATABASE .env | cut -d '=' -f2)

        # Restore storage
        info "Restoring storage..."
        tar -xzf "$backup_path/storage.tar.gz" -C "$APP_DIR"

        # Restore git commit
        if [ -f "$backup_path/git_commit.txt" ]; then
            local commit=$(cat "$backup_path/git_commit.txt")
            git checkout "$commit"
        fi

        # Optimize and restart
        optimize_application
        restart_services
        disable_maintenance_mode

        log "Rollback completed"
    else
        error "No backup found for rollback"
    fi
}

################################################################################
# Main deployment flow
################################################################################

main() {
    log "======================================================================"
    log "Starting $APP_NAME deployment"
    log "======================================================================"

    # Trap errors for rollback
    trap rollback ERR

    # Confirm deployment
    confirm "Are you sure you want to deploy to production?"

    # Execute deployment steps
    pre_deployment_checks
    backup_current_state
    enable_maintenance_mode
    pull_latest_code
    install_dependencies
    build_assets
    run_migrations
    optimize_application
    set_permissions
    restart_services
    disable_maintenance_mode
    health_check

    log "======================================================================"
    log "Deployment completed successfully! 🎉"
    log "======================================================================"

    # Display summary
    echo ""
    info "Deployment Summary:"
    info "- Application: $APP_NAME"
    info "- Directory: $APP_DIR"
    info "- Timestamp: $(date)"
    info "- Backup: $(cat /tmp/shelve_last_backup)"
    echo ""

    # Clean trap
    trap - ERR
}

################################################################################
# Script entry point
################################################################################

# Parse command line arguments
case "${1:-deploy}" in
    deploy)
        main
        ;;
    rollback)
        rollback
        ;;
    health)
        health_check
        ;;
    backup)
        backup_current_state
        ;;
    *)
        echo "Usage: $0 {deploy|rollback|health|backup}"
        exit 1
        ;;
esac
