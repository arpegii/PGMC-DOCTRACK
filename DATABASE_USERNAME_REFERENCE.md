# Database Changes: Email to Username Migration

## Current Database Structure

Your `users` table now has BOTH `email` and `username` columns. This is the recommended approach because:

1. **Email remains unique** - for password recovery and account identification
2. **Username is for login** - simpler and more user-friendly
3. **Backward compatibility** - easier to manage existing data

---

## Database Schema (Current)

```sql
users table:
├── id (Primary Key)
├── name (User's full name)
├── email (Unique) - Still present and required
├── username (Unique) - NEW: Used for login
├── profile_picture
├── email_verified_at
├── password (bcrypt hashed)
├── remember_token
├── created_at
├── updated_at
├── unit_id (Foreign Key)
└── is_admin (Boolean)
```

---

## View Database Schema in MySQL

### Using Command Line:

```bash
mysql -u root -p DOCTRACK
```

Then run:

```sql
DESCRIBE users;
```

### See the new username column:

```sql
SHOW COLUMNS FROM users WHERE Field = 'username';
```

Expected output:

```
Field    | Type         | Null | Key | Default | Extra
---------|--------------|------|-----|---------|-------
username | varchar(255) | NO   | UNI | NULL    |
```

### List all users with their usernames:

```sql
SELECT id, name, email, username FROM users;
```

---

## Understanding the Username Column

### What's in the username column?

For existing users, the system automatically generated usernames using this formula:

```
username = CONCAT(SUBSTRING_INDEX(email, '@', 1), '_', id)
```

This means:

- Take the part before @ in their email
- Add an underscore and their user ID

**Examples:**

| User ID | Email                  | Generated Username |
| ------- | ---------------------- | ------------------ |
| 1       | john@company.com       | john_1             |
| 2       | jane.smith@pgmc.gov    | jane_2             |
| 3       | admin@pgmc.gov         | admin_3            |
| 4       | alice.johnson@test.org | alice_4            |

---

## Advanced: How to Change Usernames in Bulk

If you need to change how usernames are generated, here are some SQL examples:

### View current usernames:

```sql
SELECT id, name, email, username FROM users ORDER BY id;
```

### Update a single user's username:

```sql
UPDATE users SET username = 'new_username' WHERE id = 1;
```

### Generate usernames from first name + last name:

```sql
UPDATE users SET username = LOWER(CONCAT(
  SUBSTRING_INDEX(name, ' ', 1),
  '_',
  SUBSTRING_INDEX(name, ' ', -1)
)) WHERE username LIKE '%_%';
```

### Generate simple numbered usernames:

```sql
UPDATE users SET username = CONCAT('user_', id);
```

### Generate usernames from name only (lowercase):

```sql
UPDATE users SET username = LOWER(REPLACE(name, ' ', '_'));
```

### Reset to auto-generated format:

```sql
UPDATE users SET username = CONCAT(SUBSTRING_INDEX(email, '@', 1), '_', id);
```

**⚠️ Warning:** Make sure to back up your database before running bulk UPDATE statements!

---

## Authentication Flow in Database

### When a user logs in with username/password:

1. **Check migrations table:**

    ```sql
    SELECT * FROM migrations WHERE migration LIKE '%username%';
    ```

    Should show: `2026_04_19_100000_add_username_to_users_table`

2. **Find user by username:**

    ```sql
    SELECT * FROM users WHERE username = 'john_1';
    ```

3. **Verify password (in Laravel code):**

    ```php
    Hash::check($password, $user->password)  // bcrypt comparison
    ```

4. **Create session:**
    - User ID is stored in the session
    - User is marked as authenticated
    - Session token is created

---

## Important Database Constraints

### Unique Constraints:

```sql
-- Email must be unique
ALTER TABLE users ADD UNIQUE KEY users_email_unique(email);

-- Username must be unique
ALTER TABLE users ADD UNIQUE KEY users_username_unique(username);
```

This means:

- No two users can have the same email
- No two users can have the same username
- Both are enforced by the database

### Index for Performance:

Both `email` and `username` columns are indexed as UNIQUE, which means:

- Fast lookups during login
- Prevents duplicate entries automatically
- Queries run efficiently even with thousands of users

---

## Optional: Remove Email Column (Advanced)

**⚠️ NOT RECOMMENDED** - Only do this if you're certain you don't need email for any other features.

If you wanted to completely remove the email column and use ONLY username:

```php
// Create a new migration:
php artisan make:migration remove_email_from_users_table

// In the migration file:
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropUnique(['users_email_unique']);
        $table->dropColumn('email');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('email')->unique();
    });
}

// Run:
php artisan migrate
```

**But we recommend KEEPING email because:**

- ✅ Can use for password recovery
- ✅ Can use for notifications
- ✅ Can use for 2FA via email
- ✅ Doesn't harm anything to keep it
- ✅ Easy to add email-based features later

---

## Checking if Everything is Working

### In Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
// Test 1: View all columns
Schema::getColumnListing('users')

// Test 2: Find user by username
User::where('username', 'john_1')->first()

// Test 3: Check constraints
DB::statement('SHOW INDEXES FROM users')

// Test 4: Verify usernames are unique
User::groupBy('username')->havingRaw('count(*) > 1')->get()
// Should return empty collection (no duplicates)

exit
```

---

## Quick Reference SQL Commands

```sql
-- See all users with usernames
SELECT id, name, email, username FROM users;

-- Check if username already exists
SELECT * FROM users WHERE username = 'john_1';

-- Update a user's username
UPDATE users SET username = 'new_username' WHERE id = 1;

-- Find user by email
SELECT * FROM users WHERE email = 'john@company.com';

-- Count total users
SELECT COUNT(*) as total_users FROM users;

-- See login attempts (if you have a logs table)
SELECT * FROM logs WHERE action = 'login_attempt' ORDER BY created_at DESC;

-- Check migration status
SELECT * FROM migrations WHERE migration LIKE '%username%';

-- Verify username uniqueness
SELECT username, COUNT(*) as count FROM users GROUP BY username HAVING count > 1;
```

---

## Troubleshooting Database Issues

### Issue: "Duplicate key value violates unique constraint"

**Cause:** Tried to insert a username or email that already exists
**Solution:** Use a different username or email

### Issue: "COLUMN not found: 1054 Unknown column 'username'"

**Cause:** Migration wasn't properly applied
**Solution:**

```bash
php artisan migrate
# Or manually check if column exists:
DESCRIBE users;
```

### Issue: "Integrity constraint violation"

**Cause:** Trying to insert NULL into username (should be not null)
**Solution:** Always provide a username value

---

## Summary

✅ Your application is now using **username** for authentication
✅ The `username` column has been added to your `users` table
✅ Existing users have auto-generated usernames
✅ Email column remains for future use
✅ Both fields are unique and indexed for performance
✅ Migration has been successfully applied

You can now test the login and registration with usernames!
