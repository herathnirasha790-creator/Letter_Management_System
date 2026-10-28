# North Western Province (NWP) - Engineering Department Letter Management System

## Overview
A web-based mail and letter tracking system built with PHP, MySQL, and modern Vanilla CSS for the Engineering Department of the North Western Province.

---

## Database Setup Instructions

You have two convenient ways to set up the database:

### Option 1: Automatic Setup via PHP (Recommended)
1. Ensure Apache and MySQL are running in your XAMPP Control Panel.
2. In your browser or command line, run:
   - **Browser**: Visit `http://localhost/nwp/setup_db.php`
   - **CLI**: Run `php setup_db.php` from `c:\xampp\htdocs\nwp\`
3. This will automatically create the database `engineering_db`, create all 9 required tables, perform schema migrations, and insert initial seed data (default Admin user, Section Heads, and Institute Contacts).

### Option 2: Manual Import via phpMyAdmin / MySQL CLI
1. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
2. Click on **Import**.
3. Choose either [database.sql](file:///c:/xampp/htdocs/nwp/database.sql) or [engineering_db.sql](file:///c:/xampp/htdocs/nwp/engineering_db.sql).
4. Click **Import** (or **Go**).

---

## Default Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: `admin`

---

## Database Schema Structure
The system uses the following tables in `engineering_db`:
1. `users` - System user accounts and authentication.
2. `letters` - Main letter records (inward letters, sender info, dates, status).
3. `primary_assignments` - Primary department delegations and tasks.
4. `secondary_assignments` - Secondary assignments and forwarding notes.
5. `section_heads` - Department heads / divisions (AD, AO, Chief Engineers, etc.).
6. `institute_contacts` - Institute directory for auto-filling sender designations/emails.
7. `officers` - Registered department officers.
8. `tasks` - Task types.
9. `assignments` - Relational letter-officer assignments.
