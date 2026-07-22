# Online Voting System Using PHP

A modern, secure, and user-friendly web-based voting system built with PHP and MySQL. The system features a streamlined voter registration process accessible from the login page, where users can enter their first name, last name, password, and an optional profile photo. Clicking "Generate Voter ID & Register" automatically creates a unique Voter ID, allowing users to log in using their credentials. Once authenticated, users enter the "2026 ELECTION PORTAL," which consists of two main steps: selecting nominees from registered voters (with options to accept or decline, or bypass if candidates already exist) and casting final votes with a maximum selection cap of at least 3. Additionally, the admin dashboard, accessed via /admin/home.php, provides comprehensive management tools including real-time statistics cards, live tally sections for top standings and nominees, sidebar navigation for reports, management, and settings, as well as profile controls and print functions.
---

## 📋 System Requirements

- **XAMPP** (Apache + MySQL + PHP v7.4 or higher recommended)
- Any modern **Web Browser** (Chrome, Firefox, Edge, Safari)

---

## 📂 Project Structure

- `index.php` — Voter login and starting portal
- `admin/` — Administrative dashboard and management pages
- `includes/` — Shared frontend PHP components and templates
- `admin/includes/` — Shared admin-specific PHP includes
- `db/votesystem.sql` — Database schema and default seed data

---

## 🚀 Setup & Installation Guide (XAMPP)

1. **Install XAMPP** on your local machine if you haven't already.
2. **Copy the Project Folder** into your XAMPP `htdocs` directory:
   - *Example Path:* `C:\xampp\htdocs\voting`
3. **Start Services:** Open the XAMPP Control Panel and start both **Apache** and **MySQL**.
4. **Access phpMyAdmin:** Open your browser and go to `http://localhost/phpmyadmin`.
5. **Create Database:** Create a new database named `votesystem`.
6. **Import SQL File:**
   - Click on the `votesystem` database in the left sidebar.
   - Navigate to the **Import** tab.
   - Select `db/votesystem.sql` from your project directory.
   - Click **Go** at the bottom to execute the import.
7. **Launch Voter Portal:** Open your browser and navigate to:
   - `http://localhost/voting/`
8. **Launch Admin Panel:** Navigate to:
   - `http://localhost/voting/admin/`

---

## 🔑 Default Admin Account

- **Username:** angel
- **Password:** 1234

---

## 🛠️ Troubleshooting

- **Database Connection Error:** Verify that the MySQL service is running in XAMPP and that a database named `votesystem` successfully exists in phpMyAdmin.
- **Pages Not Loading / 404 Errors:** Double-check that your project folder inside `htdocs` is strictly named `voting` to match your local URL structure.
- **Login Issues:** If authentication fails, ensure your database credentials match or reset the password hash directly inside the database table.
