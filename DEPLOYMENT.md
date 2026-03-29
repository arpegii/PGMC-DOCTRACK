# AFPPGMC Document Tracking System - Deployment Guide

Complete deployment guide for setting up the AFPPGMC Document Tracking System on Windows LAN networks.

## Table of Contents

1. [System Requirements](#1-system-requirements)
2. [Pre-Deployment Checklist](#2-pre-deployment-checklist)
3. [First-Time Setup](#3-first-time-setup)
4. [Database Configuration](#4-database-configuration)
5. [Email & Notification Configuration](#5-email--notification-configuration)
6. [Running the Application](#6-running-the-application)
7. [Background Processes](#7-background-processes)
8. [File Permissions & Storage](#8-file-permissions--storage)
9. [LAN Access & Firewall](#9-lan-access--firewall)
10. [Maintenance & Updates](#10-maintenance--updates)
11. [Performance Optimization](#11-performance-optimization)
12. [Security Hardening](#12-security-hardening)
13. [Troubleshooting](#13-troubleshooting)
14. [Backup & Recovery](#14-backup--recovery)
15. [Pre-Go-Live Checklist](#15-pre-go-live-checklist)

---

## 1) System Requirements

### Server Machine

- **OS:** Windows Server 2016+ or Windows 10/11 Professional
- **PHP:** `8.2+` (with required extensions)
- **Database:** MySQL `5.7.35+` or MariaDB `10.3+`
- **Node.js:** `18.x+` with npm `9.x+`
- **Composer:** `2.x+`

### Required PHP Extensions

```
pdo_mysql
mbstring
openssl
tokenizer
ctype
json
fileinfo
curl
xml
gd
bcmath
```

### Hardware Recommendations

- **CPU:** Multi-core processor (minimal requirement: 2 cores for small deployments)
- **RAM:** 4GB minimum (8GB recommended for 50+ concurrent users)
- **Storage:**
    - 10GB for application + OS + database
    - Additional storage for document uploads (plan for ~5-100MB per document)
    - Reserve 20% free disk space for performance

### Client Workstations

- Modern browser: Chrome, Edge, Firefox (Safari 11+)
- Minimum internet connection: 1 Mbps (recommend: 10+ Mbps for file uploads)
- No additional software required

---

## 2) Pre-Deployment Checklist

Before starting: ( )

- [ ] Server meets all system requirements
- [ ] Network connectivity tested between server and client machines
- [ ] Backup of any existing data completed
- [ ] Administrator access on server machine available
- [ ] MySQL service running and accessible
- [ ] Port 8000+ available on server (or verify alternative port)
- [ ] Project code downloaded/cloned to server

---

## 3) First-Time Setup

### Step 1: Install Dependencies

From project root (`C:\Projects\AFPPGMC-DOCTRACK` or similar):

```powershell
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

### Step 2: Configure Environment

```powershell
# Copy example environment file
copy .env.example .env

# Generate application key (required for encryption)
php artisan key:generate
```

### Step 3: Configure `.env` File

Edit `.env` with the following critical settings:

```env
# Application
APP_NAME=AFPPGMC-DOCTRACK
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.1.100:8000
APP_TIMEZONE=UTC

# Database - CRITICAL: Update these values
DB_CONNECTION=mysql
DB_HOST=127.0.0.1          # or your MySQL server IP
DB_PORT=3306
DB_DATABASE=afppgmc_doctrack
DB_USERNAME=doctrack_user
DB_PASSWORD=CHANGE_ME_TO_STRONG_PASSWORD!

# Queue - Keep as sync for simple deployments
QUEUE_CONNECTION=sync

# Mail Configuration - See Section 5 for details
MAIL_MAILER=log            # Change to 'mailgun', 'sendgrid', or 'smtp' for production
MAIL_FROM_ADDRESS=no-reply@afppgmc.local
MAIL_FROM_NAME="AFPPGMC Document Tracking"

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=480       # 8 hours

# Cache
CACHE_DRIVER=file
```

⚠️ **CRITICAL:** Change `DB_PASSWORD` to a strong, unique password. Store it securely.

### Step 4: Set Up Database

```powershell
# Run all pending migrations
php artisan migrate --force

# Seed initial data (units, document types)
php artisan db:seed --force

# Create storage symlink (required for file uploads)
php artisan storage:link

# Optimize application for production
php artisan optimize
php artisan config:cache
php artisan route:cache
```

---

## 4) Database Configuration

### MySQL Setup

1. **Connect to MySQL as administrator:**

    ```sql
    mysql -u root -p
    ```

2. **Create database and user:**

    ```sql
    CREATE DATABASE afppgmc_doctrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    CREATE USER 'doctrack_user'@'localhost' IDENTIFIED BY 'CHANGE_ME_TO_STRONG_PASSWORD!';

    -- Grant permissions
    GRANT ALL PRIVILEGES ON afppgmc_doctrack.* TO 'doctrack_user'@'localhost';

    -- For remote connections (if DB server is different from app server):
    GRANT ALL PRIVILEGES ON afppgmc_doctrack.* TO 'doctrack_user'@'192.168.%.%';

    FLUSH PRIVILEGES;
    ```

3. **Test connection:**
    ```powershell
    php artisan db:test
    ```

### Database Backup Strategy

Schedule daily backups:

```powershell
# Add to Windows Task Scheduler:
# Run every day at 2 AM

mysqldump -u doctrack_user -p afppgmc_doctrack > "C:\Backups\doctrack_$(Get-Date -Format 'yyyyMMdd').sql"
```

---

## 5) Email & Notification Configuration

### ⚠️ CRITICAL: Default Configuration Issue

**The application uses email for important notifications:**

- Document received/rejected/forwarded notifications
- Password reset emails
- Email change verification

**Default `.env.example` has `MAIL_MAILER=log` which DOES NOT send actual emails in production.**

### Option 1: Using SMTP (Most Common)

Configure your email server in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com          # or your mail server
MAIL_PORT=587
MAIL_USERNAME=noreply@afppgmc.local
MAIL_PASSWORD=your_app_specific_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@afppgmc.local
```

### Option 2: Using Mailgun (Recommended for Production)

```env
MAIL_MAILER=mailgun
MAIL_API_DOMAIN=mg.yourdomain.com
MAIL_SECRET=your_mailgun_api_key
```

### Option 3: Using SendGrid

```env
MAIL_MAILER=sendgrid
MAIL_SENDGRID_SECRET=your_sendgrid_api_key
```

### Option 4: Development/Internal Only

If email is not needed during development:

```env
MAIL_MAILER=log
# Emails will be logged to storage/logs/laravel-*.log
```

**Test mail configuration:**

```powershell
php artisan tinker
>> Mail::raw('Test email', function($m) { $m->to('you@example.com')->subject('Test'); });
// Check storage/logs/ for output if using 'log' driver
```

---

## 6) Running the Application

### Development Server (For Testing/Small Networks)

```powershell
# Start the built-in Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# Server is now accessible at:
# http://localhost:8000 (on server machine)
# http://192.168.1.100:8000 (from other network machines)
```

### Production Server (Recommended: IIS or Nginx)

For production deployments with more than 10 concurrent users, use a proper web server:

**Option A: IIS (Integrated into Windows)**

- Follow Laravel IIS deployment guide
- Use `.env` file with `APP_ENV=production`

**Option B: Nginx + FastCGI**

- Install Nginx for Windows
- Configure FastCGI for PHP
- Better performance and resource usage

For simplicity, the built-in server is acceptable for small teams (<50 users).

---

## 7) Background Processes

### Overdue Document Notifications

The application automatically notifies users of overdue documents. **This requires the scheduler to run continuously.**

#### Option A: Using `schedule:work` (Easiest)

```powershell
# Start scheduler (runs continuously)
php artisan schedule:work

# Keep this terminal/session open
```

#### Option B: Windows Task Scheduler (Recommended)

1. Open **Task Scheduler**
2. Create **New Task:**
    - Name: `AFPPGMC Scheduler`
    - Trigger: Every 1 minute
    - Action:
        ```
        Program: C:\PHP\php.exe
        Arguments: "C:\path\to\artisan" schedule:run
        Start in: C:\path\to\project\root
        ```
    - Run with highest privileges
    - Run whether user is logged in or not

**Verify scheduler is running:**

```powershell
# Check Task Scheduler logs or:
tail -f storage/logs/laravel-*.log
```

### Queue Processing (If Using Database Queues)

If you change `QUEUE_CONNECTION=database` in `.env`:

```powershell
# Start queue worker
php artisan queue:work --tries=3 --timeout=120

# Or as a Windows Service using NSSM
```

**Note:** For small deployments, keep `QUEUE_CONNECTION=sync` (default).

---

## 8) File Permissions & Storage

### Storage Directories

Ensure the following directories are writable by the PHP process:

- `storage/` (and all subdirectories)
- `bootstrap/cache/`
- `public/storage/` (symlink target)

### Set Permissions (PowerShell as Administrator)

```powershell
# Get PHP process user (usually IUSR or Network Service)
# Then grant permissions:

# For IIS anonymous user
icacls "C:\path\to\storage" /grant:r "IUSR:(OI)(CI)F" /T
icacls "C:\path\to\bootstrap\cache" /grant:r "IUSR:(OI)(CI)F" /T
```

### Document Upload Directory

- Default: `storage/app/documents/`
- Keep backups of this directory
- Monitor available disk space
- Consider mounting on large external drive if documents grow beyond 50GB

---

## 9) LAN Access & Firewall

### Windows Firewall Configuration

1. Open **Windows Defender Firewall with Advanced Security**
2. Create **Inbound Rule:**
    - Name: `AFPPGMC Document Tracking`
    - Protocol: TCP
    - Port: 8000 (or your chosen port)
    - Action: Allow
    - Apply to: Domain, Private profiles (NOT Public)

### Alternative: Command Line

```powershell
netsh advfirewall firewall add rule `
  name="AFPPGMC-Port8000" `
  dir=in action=allow protocol=TCP localport=8000 `
  profile=private,domain
```

### Network Access

- **From server machine:** `http://localhost:8000` or `http://127.0.0.1:8000`
- **From client machine:** `http://<SERVER_IP>:8000` (e.g., `http://192.168.1.100:8000`)
- **Using hostname:** Configure DNS to resolve server hostname, or use `\\SERVER_NAME\` in UNC paths

---

## 10) Maintenance & Updates

### Regular Maintenance Tasks

**Weekly:**

- Check application logs: `storage/logs/laravel-*.log`
- Verify scheduler is running and has no errors
- Monitor disk space (especially document storage)

**Monthly:**

- Install PHP/MySQL security updates
- Check for Laravel framework updates: `composer update `
- Verify backups are being created

**Quarterly:**

- Review user access logs
- Archive old/completed document batches
- Database optimization (`php artisan db:optimize`)

### Applying Updates

```powershell
# Backup first!
mysqldump -u doctrack_user -p afppgmc_doctrack > backup.sql

# Pull latest code (if using git)
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run database migrations
php artisan migrate --force

# Clear caches and optimize
php artisan cache:clear
php artisan optimize

# Restart application and scheduler
# (restart php artisan processes)
```

---

## 11) Performance Optimization

### Database Indices

Indices have been added automatically for:

- documents(created_by)
- documents(status, receiving_unit_id)
- documents(status, sender_unit_id)
- document_forward_history(document_id, from_unit_id, to_unit_id)
- notifications(notifiable_id, notifiable_type, read_at)

No additional configuration needed.

### Query Optimization

The application uses eager loading to prevent N+1 queries. All controllers load relationships efficiently.

### Caching Strategy

For large deployments (100+ users):

- Use Redis or Memcached instead of file cache
- Configure in `.env`:
    ```env
    CACHE_DRIVER=redis
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
    ```

---

## 12) Security Hardening

### Essential Security Steps

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set strong `DB_PASSWORD` (16+ characters, mixed case, numbers, symbols)
- [ ] Change `APP_KEY` (run `php artisan key:generate` in fresh .env)
- [ ] Restrict access to `.env` file (read-only, not accessible via web)
- [ ] Enable HTTPS if accessing over untrusted networks
    ```env
    APP_URL=https://afppgmc.yourdomain.com
    SESSION_SECURE_COOKIES=true
    ```
- [ ] Set strong passwords for all admin accounts
- [ ] Use VPN or firewall rules to restrict access to authorized IP ranges
- [ ] Regularly review user permissions and audit logs

### Admin Unit Protection

- Only administrators can send/receive documents to/from the Admin unit
- Non-admin users automatically cannot access Admin unit
- All admin operations are logged

---

## 13) Troubleshooting

### Application Won't Start

```powershell
# Check for configuration errors
php artisan config:test

# Check database connection
php artisan db:test

# Clear application cache
php artisan cache:clear
php artisan config:clear
```

### Database Connection Errors

```powershell
# Verify MySQL is running
Get-Service MySQL80  # or your MySQL version

# Test MySQL connection from command line
mysql -h 127.0.0.1 -u doctrack_user -p
# Enter password, then \. dump.sql to test

# Check .env file credentials
Get-Content .env | findstr DB_
```

### File Upload Issues

```powershell
# Verify storage link exists
dir public/storage

# Verify storage directory permissions
icacls "storage"

# Check available disk space
(Get-Volume C:).SizeRemaining / 1GB
```

### Emails Not Sending

```powershell
# If using log driver:
Get-Content storage/logs/laravel-*.log

# Test mail configuration:
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com'); });
```

### Scheduler Not Running

```powershell
# Check Task Scheduler logs
Get-WinEvent -LogName "Microsoft-Windows-TaskScheduler/Operational" -MaxEvents 20

# Manually test scheduler
php artisan schedule:work --verbose

# Check cron jobs defined:
php artisan schedule:list
```

---

## 14) Backup & Recovery

### Automated Daily Backup Script

Create `C:\Scripts\backup-doctrack.ps1`:

```powershell
$BackupDir = "C:\Backups\DocTrack"
$Date = Get-Date -Format "yyyyMMdd_HHmmss"

# Backup database
$SqlFile = "$BackupDir\db_$Date.sql"
mysqldump -u doctrack_user -p'PASSWORD' afppgmc_doctrack | Out-File $SqlFile

# Backup documents
$DocDir = "C:\Projects\AFPPGMC-DOCTRACK\storage\app\documents"
$ZipFile = "$BackupDir\documents_$Date.zip"
Compress-Archive -Path $DocDir -DestinationPath $ZipFile

# Keep only last 30 days
Get-ChildItem $BackupDir -Filter "*.sql", "*.zip" | Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } | Remove-Item

Write-Host "Backup complete: $SqlFile, $ZipFile"
```

Schedule in Task Scheduler (run daily at 2 AM):

```powershell
# Run as administrator
$Action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-File C:\Scripts\backup-doctrack.ps1"
$Trigger = New-ScheduledTaskTrigger -Daily -At 02:00
Register-ScheduledTask -Action $Action -Trigger $Trigger -TaskName "DocTrack-Backup" -RunLevel Highest
```

### Recovery Procedure

**To restore from backup:**

```powershell
# 1. Stop the application
# Stop all php artisan processes

# 2. Restore database
mysql -u doctrack_user -p afppgmc_doctrack < backup.sql

# 3. Restore documents
Remove-Item "C:\Projects\AFPPGMC-DOCTRACK\storage\app\documents" -Recurse
Expand-Archive "documents_YYYYMMDD_HHMMSS.zip" -DestinationPath "C:\Projects\..."

# 4. Clear application cache
php artisan cache:clear
php artisan view:clear

# 5. Restart application
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 15) Pre-Go-Live Checklist

**Do NOT deploy to production without completing these:**

### Configuration

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated and not empty
- [ ] `APP_URL` points to correct server/port
- [ ] `DB_HOST`, `DB_PORT`, `DB_DATABASE` verified
- [ ] `DB_PASSWORD` is strong (16+ characters)
- [ ] `MAIL_MAILER` configured (not 'log' for production)

### Database

- [ ] Database created and migrations run
- [ ] Database seeders executed (units, document types)
- [ ] Database connection tested and working
- [ ] Backup plan documented

### Application

- [ ] All dependencies installed with `--no-dev`
- [ ] Assets built with `npm run build`
- [ ] Storage link created (`php artisan storage:link`)
- [ ] File permissions set correctly
- [ ] `storage/` and `bootstrap/cache/` writable

### Deployment

- [ ] Server machine meets requirements
- [ ] PHP and MySQL services running
- [ ] Firewall rules configured
- [ ] Network connectivity tested from client machines

### Operations

- [ ] Scheduler/cron jobs configured to run
- [ ] Backup script scheduled
- [ ] Administrator account created with strong password
- [ ] Monitor strategy defined (who watches logs, disk space, errors)
- [ ] Escalation contacts defined

### Testing

- [ ] Application loads without errors
- [ ] Can create/send document (end-to-end test)
- [ ] Notifications are being sent (if email configured)
- [ ] File uploads work correctly
- [ ] Search and filters work
- [ ] Multiple concurrent users can connect

---

## Support & Monitoring

For ongoing support and monitoring:

1. **Check application logs:** `storage/logs/laravel-*.log`
2. **Monitor disk space:** Watch `storage/app/documents/` growth
3. **Review errors:** Look for exceptions or warnings in logs
4. **Database performance:** Run `ANALYZE TABLE` monthly if using MySQL 5.7

**For issues or questions:** Contact system administrator or development team.

---

**Last Updated:** March 30, 2026
**Version:** 2.0 (Complete Deployment Guide)
