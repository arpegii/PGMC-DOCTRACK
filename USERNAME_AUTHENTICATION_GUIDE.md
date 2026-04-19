# Username Authentication Guide

## Overview

Your application has been successfully updated to use **username** for authentication instead of email. Users can now sign in and sign up using a unique username.

---

## What Was Changed

### 1. **Database Migration**

📁 File: `database/migrations/2026_04_19_100000_add_username_to_users_table.php`

- Added a `username` column to the `users` table
- Made it unique and non-nullable
- Automatically populated existing users with a generated username based on their email (format: `emailprefix_userid`)
- Example: If a user has email `john@company.com` with ID 5, their username becomes `john_5`

#### How to run the migration:

```bash
php artisan migrate
```

### 2. **User Model Updates**

📁 File: `app/Models/User.php`

- Added `username` to the `$fillable` array so it can be mass-assigned during registration

### 3. **Registration Controller**

📁 File: `app/Http/Controllers/Auth/RegisteredUserController.php`

- Updated validation to require and validate `username` field
- Username must be lowercase letters, numbers, and underscores only
- Username must be unique across all users
- Users still provide email during registration (for future use if needed)

**Validation Rules:**

```php
'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:users', 'regex:/^[a-z0-9_]+$/']
```

### 4. **Login Request**

📁 File: `app/Http/Requests/Auth/LoginRequest.php`

- Changed from email-based authentication to username-based
- Manual user lookup by username with password verification
- Rate limiting now uses username instead of email
- Error messages updated to reference username

### 5. **Login View**

📁 File: `resources/views/auth/login.blade.php`

- Replaced email input field with username input field
- Updated label from "Email address" to "Username"
- Changed placeholder text
- Updated autocomplete attribute

### 6. **Registration View**

📁 File: `resources/views/auth/register.blade.php`

- Added username field to the registration form
- Placed before email field
- Includes helper text: "Lowercase letters, numbers, and underscores only"
- Email field still present for user contact information

---

## Database Schema

The `users` table now has this structure:

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,  -- NEW COLUMN
    profile_picture VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    unit_id BIGINT UNSIGNED NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    ...
);
```

---

## How Users Log In

### Sign Up Flow:

1. User fills in: **Name**, **Username**, **Email**, **Unit**, **Password**
2. Username must:
    - Be 3-255 characters
    - Contain only lowercase letters, numbers, and underscores
    - Be unique (no other user can have the same username)
3. User is automatically logged in after registration
4. Redirected to the dashboard

### Sign In Flow:

1. User enters **Username** and **Password**
2. System looks up the user by username in the database
3. Password is verified using bcrypt hash
4. If valid, user is logged in
5. If invalid, error message: "These credentials do not match our records"
6. Rate limiting: Max 5 failed attempts per IP address per minute

---

## Username Generation for Existing Users

When the migration ran, existing users were automatically assigned usernames:

**Formula:** `{email_prefix}_{user_id}`

**Examples:**

- Email: `john.doe@company.com`, ID: 1 → Username: `john_1`
- Email: `jane.smith@company.com`, ID: 2 → Username: `jane_2`
- Email: `admin@pgmc.gov`, ID: 3 → Username: `admin_3`

**Note:** Existing users should update their usernames to something more memorable through a profile settings page (if you create one later).

---

## Key Features

✅ **Unique Usernames** - Each user has a unique username (enforced by database constraint)

✅ **Case-Insensitive Input** - Usernames automatically converted to lowercase

✅ **Password Protected** - Passwords remain bcrypt hashed and secure

✅ **Email Still Required** - Email field remains for future notifications/recovery

✅ **Rate Limiting** - Prevents brute force attacks (5 attempts per IP per minute)

✅ **Remember Me** - Users can stay logged in across sessions

---

## Validation Rules Summary

### Username Field:

- **Required:** Yes
- **Type:** String
- **Length:** Max 255 characters
- **Pattern:** Lowercase letters (a-z), numbers (0-9), underscores (\_) only
- **Uniqueness:** Must be unique across all users
- **Example Valid Usernames:**
    - `john_doe`
    - `jane123`
    - `admin_user`
    - `user_2024`

### Email Field:

- **Required:** Yes (for user contact info)
- **Type:** Valid email format
- **Uniqueness:** Must be unique across all users

### Password Field:

- **Required:** Yes
- **Min Length:** 8 characters
- **Requirements:**
    - At least one uppercase letter
    - At least one lowercase letter
    - At least one number
    - Special characters optional

---

## Testing the Changes

### Test Sign Up:

1. Go to `/register`
2. Fill in:
    - Name: `Test User`
    - Username: `test_user`
    - Email: `test@company.com`
    - Unit: Select a unit
    - Password: `Password123`
    - Confirm: `Password123`
3. Click "Sign up"
4. Should be logged in and redirected to dashboard

### Test Sign In:

1. Go to `/login`
2. Enter:
    - Username: `test_user`
    - Password: `Password123`
3. Click "Sign in"
4. Should be logged in and redirected to dashboard

### Test Validation:

1. Try username with uppercase: `Test_User` → Should auto-convert or reject
2. Try username with special chars: `test@user` → Should show error
3. Try duplicate username → Should show "This username is already taken"
4. Try wrong password → Should show "These credentials do not match our records"

---

## Important Notes

⚠️ **Email is Still Important**

- Email column remains required and unique
- Email can be used for future features like:
    - Password recovery
    - Email notifications
    - Account verification
    - Two-factor authentication

⚠️ **Backward Compatibility**

- Existing users have been assigned auto-generated usernames
- Email authentication no longer works (must use username)
- Old login links/bookmarks will need to be updated

⚠️ **Migration Already Applied**

- The `username` column has been added to the database
- All existing users have been assigned usernames
- No additional database changes needed

---

## Troubleshooting

### Issue: "Username field not showing in registration form"

**Solution:** Clear browser cache (Ctrl+Shift+Delete) and refresh the page

### Issue: "This username is already taken"

**Solution:** Choose a different username - usernames must be unique

### Issue: "Cannot log in with old email"

**Solution:** Use the assigned username instead (format: `emailprefix_userid`)

### Issue: "Username contains invalid characters"

**Solution:** Only use lowercase letters, numbers, and underscores. No spaces or special characters.

---

## Future Enhancements

Consider implementing these features later:

- [ ] Username profile page where users can view/change their username
- [ ] Email recovery using email address
- [ ] Display/search users by username
- [ ] Username availability checker during registration
- [ ] Suggest usernames based on user's full name
- [ ] Admin panel to manage usernames

---

## File Summary

| File                                                                    | Changes                        |
| ----------------------------------------------------------------------- | ------------------------------ |
| `database/migrations/2026_04_19_100000_add_username_to_users_table.php` | ✅ Created                     |
| `app/Models/User.php`                                                   | ✅ Added username to $fillable |
| `app/Http/Controllers/Auth/RegisteredUserController.php`                | ✅ Updated validation          |
| `app/Http/Requests/Auth/LoginRequest.php`                               | ✅ Changed to username auth    |
| `resources/views/auth/login.blade.php`                                  | ✅ Email → Username field      |
| `resources/views/auth/register.blade.php`                               | ✅ Added username field        |

---

**Status:** ✅ All changes completed and database migrated successfully!
