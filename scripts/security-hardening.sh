#!/bin/bash

################################################################################
# Security Hardening Script
# Phase 12.4 - Production Security Configuration
################################################################################

set -e

APP_DIR="/var/www/shelve"
LOG_FILE="/var/log/shelve-security.log"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date +'%H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"; }
error() { echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"; }
warning() { echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"; }

################################################################################
# Configure SSL/TLS
################################################################################
configure_ssl() {
    log "Configuring SSL/TLS..."

    # Install Certbot if not present
    if ! command -v certbot &> /dev/null; then
        log "Installing Certbot..."
        apt-get update
        apt-get install -y certbot python3-certbot-nginx
    fi

    # SSL configuration for Nginx
    cat > /etc/nginx/snippets/ssl-params.conf <<'EOF'
# SSL/TLS Configuration
ssl_protocols TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers on;
ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
ssl_ecdh_curve secp384r1;
ssl_session_timeout 10m;
ssl_session_cache shared:SSL:10m;
ssl_session_tickets off;
ssl_stapling on;
ssl_stapling_verify on;

# Security Headers
add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
EOF

    log "✓ SSL/TLS configuration created"
}

################################################################################
# Configure Rate Limiting
################################################################################
configure_rate_limiting() {
    log "Configuring rate limiting..."

    cd "$APP_DIR"

    # Update kernel settings
    php artisan vendor:publish --tag=sanctum-config --force

    cat > app/Http/Middleware/RateLimitMiddleware.php <<'EOF'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class RateLimitMiddleware
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, $maxAttempts),
        ]);
    }

    protected function resolveRequestSignature(Request $request)
    {
        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . $request->path() .
            '|' . $request->ip()
        );
    }
}
EOF

    log "✓ Rate limiting configured"
}

################################################################################
# Configure CORS
################################################################################
configure_cors() {
    log "Configuring CORS policies..."

    cd "$APP_DIR"

    cat > config/cors.php <<'EOF'
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('APP_URL'),
        // Add production domains
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    'max_age' => 0,

    'supports_credentials' => true,
];
EOF

    log "✓ CORS configured"
}

################################################################################
# Configure Security Headers
################################################################################
configure_security_headers() {
    log "Configuring security headers..."

    cd "$APP_DIR"

    # Create SecurityHeaders middleware
    cat > app/Http/Middleware/SecurityHeaders.php <<'EOF'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net unpkg.com",
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net",
            "connect-src 'self'",
            "frame-ancestors 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
EOF

    log "✓ Security headers configured"
}

################################################################################
# Configure Fail2Ban
################################################################################
configure_fail2ban() {
    log "Configuring Fail2Ban..."

    # Install Fail2Ban
    if ! command -v fail2ban-client &> /dev/null; then
        apt-get install -y fail2ban
    fi

    # Laravel filter
    cat > /etc/fail2ban/filter.d/laravel.conf <<'EOF'
[Definition]
failregex = ^.*"ip":"<HOST>".*"level":"error".*$
            ^.*authentication attempt from <HOST>.*$
ignoreregex =
EOF

    # Laravel jail
    cat > /etc/fail2ban/jail.d/laravel.conf <<'EOF'
[laravel]
enabled = true
port = http,https
filter = laravel
logpath = /var/www/shelve/storage/logs/laravel.log
maxretry = 5
bantime = 3600
findtime = 600
EOF

    systemctl restart fail2ban

    log "✓ Fail2Ban configured"
}

################################################################################
# Harden file permissions
################################################################################
harden_permissions() {
    log "Hardening file permissions..."

    cd "$APP_DIR"

    # Set ownership
    chown -R www-data:www-data .

    # Set directory permissions
    find . -type d -exec chmod 755 {} \;

    # Set file permissions
    find . -type f -exec chmod 644 {} \;

    # Restrict sensitive files
    chmod 600 .env
    chmod 600 config/*.php

    # Storage and cache writable
    chmod -R 775 storage bootstrap/cache

    log "✓ File permissions hardened"
}

################################################################################
# Main execution
################################################################################
main() {
    log "========================================="
    log "SHELVE Security Hardening"
    log "Phase 12.4"
    log "========================================="

    if [ "$EUID" -ne 0 ]; then
        error "Must run as root!"
        exit 1
    fi

    configure_ssl
    configure_rate_limiting
    configure_cors
    configure_security_headers
    configure_fail2ban
    harden_permissions

    log "========================================="
    log "✓ Security hardening completed!"
    log "========================================="
}

main "$@"
