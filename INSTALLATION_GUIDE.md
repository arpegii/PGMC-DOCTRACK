# PGMC Document Tracking System - Installation Guide

This guide provides step-by-step instructions for installing and setting up the PGMC Document Tracking System.

## Prerequisites

Before starting the installation, ensure you have the following installed on your system:

- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL or MariaDB
- Git

---

## Installation Steps

### 1. Install PHP Dependencies

Run Composer to install all required PHP dependencies:

```bash
composer install
```

### 2. Configure Environment Variables

Copy the example environment configuration file and create your `.env` file:

```bash
copy .env.example .env
```

Edit the `.env` file to configure your database connection and other settings as needed.

### 3. Generate Application Key

Generate the encryption key for your application:

```bash
php artisan key:generate
```

### 4. Run Database Migrations

Create the necessary database tables:

```bash
php artisan migrate
```

### 5. Seed the Database

Populate the database with initial seed data:

```bash
php artisan db:seed
```

### 6. Create Symbolic Link for Storage

Link the storage directory to make files publicly accessible:

```bash
php artisan storage:link
```

### 7. Install JavaScript Dependencies

Install Node.js dependencies:

```bash
npm install
```

### 8. Build Frontend Assets

Compile frontend assets using Vite:

```bash
npm run build
```

---

## PHP Configuration

### Enable GD Extension

The GD extension is required for image handling. Edit your `php.ini` file and locate the line:

```
;extension=gd
```

**Remove the semicolon (;)** to enable the extension:

```
extension=gd
```

This enables the GD library for image processing features in the application.

---

## Install Additional Composer Packages

### Install PHPSpreadsheet

For Excel file handling:

```bash
composer require phpoffice/phpspreadsheet
```

### Install TCPDF

For PDF generation:

```bash
composer require tecnickcom/tcpdf
```

---

## Create Admin Account

To create the initial admin account, use the Laravel Tinker shell or run the command via Artisan.

### Option 1: Using Laravel Tinker

```bash
php artisan tinker
```

Then paste the following code:

```php
\App\Models\User::create([
    'name' => 'Admin User',
    'username' => 'admin',
    'password' => Hash::make('password123'),
    'unit_id' => 1,
    'is_admin' => 1
]);
```

Press Enter to execute and exit Tinker by typing `exit`.

### Option 2: Direct Database Insertion

You can also directly insert this into your database using a database management tool like phpMyAdmin.

---

## Verification

After completing all steps, verify your installation:

1. Start the development server:

    ```bash
    php artisan serve
    ```

2. Open your browser and navigate to:

    ```
    http://localhost:8000
    ```

3. Log in with the admin credentials:
    - **Username:** admin
    - **Password:** password123

4. **Important:** Change the default admin password immediately after first login for security.

---

## Troubleshooting

- **Database connection errors:** Verify your database credentials in the `.env` file
- **Storage link errors:** Ensure the `storage` directory has proper write permissions
- **Permission denied errors:** Check file and directory permissions (use `chmod 755` on Linux/Mac)
- **Missing extensions:** Verify that required PHP extensions (gd, pdo_mysql, etc.) are enabled in `php.ini`

---

## Next Steps

- Configure email settings in `.env` if email functionality is needed
- Set up proper file permissions for production deployment
- Review the `DEPLOYMENT.md` file for production deployment guidelines
- Back up your database regularly

---

**For additional support or questions, please contact the development team.**
