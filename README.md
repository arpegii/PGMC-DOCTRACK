# AFPPGMC Document Tracking System

A comprehensive web-based document tracking and management system designed for the AFPPGMC (Armed Forces Pension Payment and Gratuity Management Center) to efficiently manage document workflows between departments and units.

**Version:** 2.0  
**Last Updated:** March 30, 2026  
**Built with:** Laravel 11, Tailwind CSS, Alpine.js

---

## 📋 Table of Contents

1. [System Overview](#system-overview)
2. [Key Features](#key-features)
3. [Quick Start](#quick-start)
4. [User Roles](#user-roles)
5. [How to Use](#how-to-use)
6. [Document Workflow](#document-workflow)
7. [Common Tasks](#common-tasks)
8. [Troubleshooting](#troubleshooting)
9. [System Administration](#system-administration)
10. [Security & Best Practices](#security--best-practices)
11. [Contact & Support](#contact--support)

---

## 📊 System Overview

The AFPPGMC Document Tracking System is a centralized platform for:

- **Sending documents** between departments/units
- **Tracking document status** throughout the workflow
- **Managing document history** and audit trails
- **Receiving notifications** about important document events
- **Forwarding documents** to other departments
- **Resubmitting rejected documents** with version history
- **Generating reports** for administrative purposes

### Core Components

| Component    | Purpose                                           |
| ------------ | ------------------------------------------------- |
| **Incoming** | Documents received by your unit, awaiting action  |
| **Received** | Documents accepted and marked as processed        |
| **Outgoing** | Documents sent from your unit (in progress)       |
| **Rejected** | Documents sent back by other units for revision   |
| **History**  | Complete audit trail of all documents and actions |
| **Track**    | Real-time status tracking for specific documents  |

---

## ✨ Key Features

### Document Management

- ✅ Create and send documents with automatic tracking numbers
- ✅ Upload multiple file types (PDF, DOC, DOCX, JPG, PNG)
- ✅ Automatic versioning for resubmitted documents
- ✅ File size limits (25MB individual documents)

### Workflow Automation

- ✅ Receive notifications on document actions
- ✅ Track document status from creation to completion
- ✅ Forward documents between departments
- ✅ Reject documents with detailed feedback
- ✅ Resubmit rejected documents with change history

### User Management

- ✅ Role-based access control (Admin, Standard Users)
- ✅ Unit-based authorization (department segregation)
- ✅ Email verification for new accounts
- ✅ Email change verification process

### Reporting & Analytics

- ✅ Generate comprehensive reports by unit and date range
- ✅ Track document processing times
- ✅ Audit trail for compliance
- ✅ Search and filter capabilities

### Performance & Security

- ✅ Optimized database queries for fast performance
- ✅ Comprehensive security hardening
- ✅ Transaction-safe operations
- ✅ Encrypted sensitive data

---

## 🚀 Quick Start

### Accessing the System

1. **Open your browser** and navigate to the server address:

    ```
    http://192.168.1.100:8000
    (Replace 192.168.1.100 with your actual server IP)
    ```

2. **You'll be redirected to the login page**

3. **Enter your credentials:**
    - Email: Your registered email address
    - Password: Your password

4. **Click "Login"**

### First Login

- If this is your first login, you may need to verify your email
- Check your email inbox for a verification link
- Click the link to verify your account
- Return to the login page and try again

### Password Reset

1. On the login page, click **"Forgot your password?"**
2. Enter your email address
3. Check your email for a password reset link
4. Click the link and create a new password
5. Return to login with your new password

---

## 👥 User Roles

### Standard User

- View documents assigned to their unit
- Send documents to other units
- Receive and approve documents
- Reject documents with feedback
- Forward documents to other units
- Resubmit rejected documents
- **Restrictions:** Cannot access Admin unit; limited to assigned unit

### Administrator

- View all document transactions across units
- Monitor documents that were sent, received, rejected, forwarded, and are incoming
- Access cross-unit reporting and audit visibility
- **Restrictions:** Cannot manually create new users; admin access is focused on transaction monitoring

### How to Know Your Role

Check the top-right corner of the dashboard:

- Your name appears there
- Next to it: "(Admin)" if administrator, nothing if standard user

---

## 📖 How to Use

### Dashboard

The dashboard displays your units' **Incoming** documents by default.

**Key elements:**

- **Status tabs** at top: Switch between Incoming, Received, Outgoing, Rejected, History
- **Search bar**: Find documents by number, title, or unit name
- **Filter dropdown**: Filter by sending/receiving unit
- **Pagination controls**: Navigate through document lists

### Sending a Document

1. Click **"New Document"** button (green + icon at bottom right)
2. Fill in the form:
    - **Document Number**: Auto-populated, cannot be changed
    - **Title**: Brief description of the document
    - **Receiving Unit**: Which unit receives this document
    - **Document Type**: Category/classification
    - **File**: Upload the document (PDF, PNG, DOC, DOCX, JPG)
        - Maximum size: 25MB
        - Required: Must upload a file
3. Click **"Send Document"**
4. ✅ Document sent! You'll see a success message
5. **Notification sent** to receiving unit users

### Receiving a Document

1. Check **"Incoming"** tab (documents sent TO your unit)
2. Find the document you want to process
3. Click document title to view details
4. Click **"Mark as Received"** button
    - Document moves to "Received" status
    - Original sender is notified
    - Document no longer appears in Incoming

### Rejecting a Document

1. Open document from **"Incoming"** tab
2. Click **"Reject Document"** button
3. In the modal:
    - **Rejection Reason**: Explain why you're rejecting (required)
    - Click **"Confirm Rejection"**
4. ✅ Document rejected!
    - Moves to "Rejected" status for you
    - Moves to "Rejected" status for sender
    - Original sender is notified with your rejection reason

### Resubmitting a Rejected Document

1. Go to **"Rejected"** tab (your sent documents that were rejected)
2. Click on the rejected document
3. Click **"Resubmit"** button
4. In the form:
    - **Title**: Updated title (can change)
    - **Document Type**: Can change if needed
    - **Receiving Unit**: Can send to different unit
    - **File**: Can upload new version or keep original
    - **Resubmit Notes**: Explain changes made
5. Click **"Resubmit Document"**
6. ✅ Document resubmitted!
    - Automatically goes to receiving unit's Incoming
    - Previous attempt history is preserved
    - Unit can see rejection reason and your resubmission notes

### Forwarding a Document

1. Open a document from **"Received"** tab (already accepted by you)
2. Click **"Forward Document"** button
3. In the modal:
    - **Forward To**: Select which unit receives it
    - **Notes**: Optional notes about why you're forwarding
4. Click **"Forward"**
5. ✅ Document forwarded!
    - Goes to other unit's Incoming queue
    - Both original sender and new unit are notified
    - Complete forwarding history is maintained
    - You can still view the document

### Viewing Document Details

1. Click any **document title** to open full details
2. You'll see:
    - Document information (title, type, sender, receiver)
    - Original file (click to download)
    - Status timeline
    - All forwarding history
    - Rejection (if rejected) and resubmission details
    - Who performed each action and when
3. From this view, you can:
    - Download the file
    - Take action (receive, reject, forward)
    - View complete audit trail

### Tracking Documents

1. Go to **"Track"** tab
2. Enter a **Document Number** or select from recent
3. Click **"Track"**
4. You'll see:
    - Current status
    - Who has it now
    - Timeline of all actions
    - All forwarding steps

### Viewing History

1. Click **"History"** tab to see ALL documents you've touched
2. Includes:
    - Sent documents (yours and others sent to you)
    - Received documents
    - Rejected and resubmitted documents
    - All forwarding records
3. Use **filters** to find specific documents
4. Use **search** to find by number, title, or unit

### Managing Notifications

1. Click the **bell icon** (top right) to see notifications
2. Notifications include:
    - Document received notifications
    - Document rejected notifications
    - Document forwarded notifications
3. Click a notification to jump to that document
4. Mark notifications as read or delete them

---

## 🔄 Document Workflow

### Complete Document Journey

```
[CREATED] → [SENT] → [INBOX] → {Received or Rejected}
              ↓
    [OUTGOING - pending reply]

If Received:
    [RECEIVED] → [FORWARDED?] → [HISTORY]

If Rejected:
    [REJECTED] → [RESUBMIT] → [INBOX] → {Received or Rejected again}
                                  ↓
                           [OUTGOING - 2nd attempt]
```

### Status Definitions

| Status       | Meaning                                          | Who Sees It    | Action Needed                  |
| ------------ | ------------------------------------------------ | -------------- | ------------------------------ |
| **Incoming** | Document in your unit's inbox, not yet processed | Receiving unit | Accept or Reject               |
| **Received** | You've accepted and processed this document      | Both units     | Forward or Archive             |
| **Outgoing** | Waiting for receiving unit to accept/reject      | Sending unit   | Monitor Progress               |
| **Rejected** | Receiving unit sent back with feedback           | Sending unit   | Review and Resubmit or Discard |
| **History**  | All documents regardless of status               | All involved   | Reference/Archive              |

---

## ✅ Common Tasks

### "How do I find a document I sent last week?"

1. Click **"History"** tab
2. Use the **date filter** if available, or
3. Use **search** to find by document number or title
4. Or go to **"Outgoing"** to see pending documents

### "I need to reject this document but don't know what to write"

1. Be specific about what sections need revision
2. Reference specific page numbers if needed
3. Suggest corrections if applicable
4. Example:
    ```
    "Page 3: Recipient DOB is incorrect.
     Please verify RO's records.
     Also need signature from Unit Commander on page 5."
    ```

### "I sent a document but haven't heard anything"

1. Click **"Track"** tab
2. Enter document number to see current status
3. If it's been weeks:
    - Go to **"Outgoing"** tab to see it
    - Consider sending a follow-up message to the receiving unit via email
    - Contact admin if urgent

### "I want to change where a document goes"

1. If document is still in **"Outgoing"** (not yet received):
    - You cannot change it; contact receiving unit
2. If document is **"Received"** by you:
    - You can **Forward** it to a different unit
3. If document is **"Rejected"**:
    - **Resubmit** it to a different unit in the form

### "Can I download multiple documents at once?"

Not directly. You must:

1. Download documents one at a time (click document → click download button)
2. Or contact admin for bulk export if needed

### "Someone else from my unit needs access"

1. Contact your **system administrator**
2. They can create a new user account for that person
3. Administrator will assign them to your unit
4. New user will receive email with login instructions

---

## 🔧 Troubleshooting

### "I can't log in"

**First attempt:**

- ✅ Verify your email is spelled correctly
- ✅ Verify caps lock is NOT on
- ✅ Try **"Forgot your password?"** to reset it

**If still no luck:**

- 📧 Ask your admin to verify your account exists
- 📧 Ask admin to reset your password
- 📧 Check if account is active (not disabled)

### "The page won't load / I see errors"

**Quick fixes:**

1. **Refresh the page** (press F5)
2. **Clear browser cache** (Ctrl+Shift+Delete, select all, clear)
3. **Try a different browser** (Chrome, Edge, etc.)
4. **Check internet connection**
5. **Try on different computer** to verify it's not your machine

**If still failing:**

- 📧 Contact system administrator
- 📧 Provide screenshot of error
- 📧 Note the URL where error occurred
- 📧 Note current time error occurred

### "I uploaded a file but it won't send"

**Common causes:**

- ❌ File size exceeds 25MB → Use a smaller file or compress
- ❌ File type not allowed → Must be PDF, DOC, DOCX, JPG, PNG
- ❌ Network disconnection → Try again
- ❌ Slow upload → Wait for completion (large files may take 5+ minutes)

**Try:**

```
1. Refresh page
2. Try a smaller file to verify upload works
3. Try different file type
4. Check internet speed
5. Contact admin if persists
```

### "I can't see a document I expect to see"

**Check:**

- ❌ Are you looking in the right tab? (Incoming vs. Outgoing vs. Received)
- ❌ Is the document status what you think it is?
- ❌ Could it be from a different unit? (Check filters)
- ❌ Could you not have access? (Only your unit sees Incoming)

**Try:**

1. Go to **History** tab (shows all documents you've touched)
2. Use **Search** with document number or keywords
3. Check **Filter** settings
4. Scroll down to find old documents

### "I deleted something by mistake"

**Unfortunately:**

- ❌ Deletions are permanent in this system
- ❌ Contact admin to check backup

**Prevention:**

- Always verify before clicking delete
- Documents are archived in History, not truly disposed

### "Email notifications aren't arriving"

**Check:**

1. ✅ Check email **Spam/Junk folder** (emails might be flagged)
2. ✅ Verify email address in your **profile** is correct
3. ✅ Contact admin to verify mail system is working

**Why you should get notifications:**

- When someone sends YOU a document
- When document you sent is received or rejected
- When document is forwarded

### "The system is running slowly"

**Quick fixes:**

- ✅ Close other browser tabs/programs
- ✅ Refresh the page
- ✅ Try at different time (less network traffic)
- ✅ Try different browser

**If consistently slow:**

- 📧 Contact admin to check server performance
- 📧 It might mean disk space is filling up or database needs optimization

---

## 🛡️ System Administration

### Admin-Only Features

**As an administrator, you have access to:**

1. **Transaction Monitoring** - View document activity across all units
2. **Sent Documents** - See documents sent by each unit
3. **Received Documents** - See documents received and processed by each unit
4. **Rejected and Forwarded Documents** - Review rejected items and forwarding history
5. **Incoming Documents** - Monitor documents currently waiting for action

### Admin Access Scope

The administrator account is intended for **visibility and monitoring only**.
Admins **cannot manually create new users** from the system.
Admins can review cross-unit document movement and transaction history.
Admins can use the dashboard to monitor workflow status across all units.

---

## 🔐 Security & Best Practices

### For All Users

✅ **DO:**

- Keep your password confidential
- Log out when done
- Verify document recipients before sending
- Check rejection reasons carefully
- Keep your email address current
- Report suspicious activity to admin

❌ **DON'T:**

- Share your password
- Leave browser open on public computers
- Click untrusted links in emails
- Bypass authentication controls
