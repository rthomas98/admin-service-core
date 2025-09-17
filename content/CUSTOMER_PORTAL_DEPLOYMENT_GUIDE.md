# Customer Portal Deployment Guide

## Overview
This guide covers the deployment process for the Customer Portal system for RAW Disposal and LIV Transport services.

## Pre-Deployment Checklist

### 1. Environment Preparation
- [ ] PostgreSQL database server configured
- [ ] Redis server installed (for caching)
- [ ] PHP 8.3.24 installed
- [ ] Node.js 20+ installed
- [ ] Composer installed
- [ ] SSL certificates configured

### 2. Environment Variables
Ensure the following are set in `.env`:
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=your_database_host
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Service Core"

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=240

# Queue
QUEUE_CONNECTION=database
```

## Deployment Steps

### Step 1: Code Deployment
```bash
# Clone or update repository
git pull origin main

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci --production
```

### Step 2: Database Setup
```bash
# Run migrations
php artisan migrate --force

# Seed initial data (only on first deployment)
php artisan db:seed --class=CompanySeeder
php artisan db:seed --class=NotificationTemplateSeeder
```

### Step 3: Build Assets
```bash
# Build frontend assets
npm run build

# Generate SSR bundle
npm run build:ssr
```

### Step 4: Laravel Optimization
```bash
# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 5: Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### Step 6: Queue Worker Setup
Create systemd service for queue worker:

```ini
# /etc/systemd/system/service-core-queue.service
[Unit]
Description=Service Core Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/path/to/admin-service-core
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Enable and start the service:
```bash
systemctl enable service-core-queue
systemctl start service-core-queue
```

### Step 7: Cron Job Setup
Add to crontab for scheduled tasks:
```cron
* * * * * cd /path/to/admin-service-core && php artisan schedule:run >> /dev/null 2>&1
```

## Security Configuration

### 1. Rate Limiting
Add to `bootstrap/app.php`:
```php
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('customer-api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('customer-auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

### 2. CORS Configuration
Update `config/cors.php`:
```php
'paths' => ['api/*', 'customer-portal/*'],
'allowed_origins' => [env('APP_URL')],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

### 3. Session Security
Update `config/session.php`:
```php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
```

## Web Server Configuration

### Nginx Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name admin-service-core.yourdomain.com;
    root /path/to/admin-service-core/public;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
}
```

## Performance Optimization

### 1. OPcache Configuration
Add to PHP configuration:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.enable_cli=1
```

### 2. Redis Configuration
```bash
# Install Redis
apt-get install redis-server

# Configure Redis
echo "maxmemory 256mb" >> /etc/redis/redis.conf
echo "maxmemory-policy allkeys-lru" >> /etc/redis/redis.conf
systemctl restart redis
```

### 3. Database Optimization
```sql
-- Add indexes for performance
CREATE INDEX idx_invoices_customer_status ON invoices(customer_id, status);
CREATE INDEX idx_service_requests_customer_status ON service_requests(customer_id, status);
CREATE INDEX idx_customer_invites_token ON customer_invites(token);
CREATE INDEX idx_customer_invites_expires ON customer_invites(expires_at);
```

## Monitoring & Maintenance

### 1. Health Check Endpoint
Create a health check route:
```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::has('health_check') ? 'working' : 'not working',
        'queue' => Queue::size() < 1000 ? 'healthy' : 'backed up',
    ]);
});
```

### 2. Log Rotation
Configure logrotate:
```bash
# /etc/logrotate.d/service-core
/path/to/admin-service-core/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.3-fpm
    endscript
}
```

### 3. Backup Strategy
```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/service-core"
pg_dump -h localhost -U dbuser -d service_core > $BACKUP_DIR/db_backup_$DATE.sql
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Keep only last 30 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete
```

## Post-Deployment Verification

### 1. Functional Tests
- [ ] Customer registration via invite works
- [ ] Customer login/logout works
- [ ] Dashboard loads with correct statistics
- [ ] Invoices are visible and downloadable
- [ ] Service requests can be created and managed
- [ ] File uploads work correctly
- [ ] Email notifications are sent

### 2. Performance Tests
- [ ] Dashboard loads in < 2 seconds
- [ ] API responses < 500ms
- [ ] Concurrent user testing (100+ users)
- [ ] Database query optimization verified

### 3. Security Tests
- [ ] Rate limiting is active
- [ ] File upload restrictions work
- [ ] Multi-tenant isolation verified
- [ ] HTTPS enforced
- [ ] Session timeout works

## Rollback Plan

If deployment issues occur:

1. **Database Rollback**:
```bash
php artisan migrate:rollback --step=5
```

2. **Code Rollback**:
```bash
git checkout previous_stable_tag
composer install
npm ci
npm run build
php artisan config:cache
```

3. **Clear Caches**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Support & Troubleshooting

### Common Issues

1. **500 Errors**: Check `storage/logs/laravel.log`
2. **Permission Issues**: Verify file ownership and permissions
3. **Queue Not Processing**: Check queue worker status
4. **Emails Not Sending**: Verify SMTP configuration
5. **Performance Issues**: Check Redis connection and OPcache

### Debug Commands
```bash
# Check application status
php artisan about

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test email
php artisan tinker
>>> Mail::raw('Test email', function($message) {
>>>     $message->to('test@example.com')->subject('Test');
>>> });

# Clear all caches
php artisan optimize:clear
```

## Contact Information

For deployment support:
- Technical Lead: [Contact Information]
- DevOps Team: [Contact Information]
- Emergency Hotline: [Phone Number]

---

Last Updated: December 11, 2024
Version: 1.0.0