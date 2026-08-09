# 🏢 DormDash — Hostel Management System

A modern, full-stack PHP & MariaDB Hostel Management web application built with a dark glassmorphism design system.

---

## 📦 How to Share & Run the Project on Teammate Laptop

### 1. **Files to Share**:
Share the **entire `HostelProject` folder** (as a ZIP file). It includes:
- Source code (`controllers/`, `models/`, `views/`, `assets/`, `config/`, `includes/`)
- Database backup file (`hostel_management.sql`)

---

## 🚀 Setup Instructions for Teammates

1. **Install XAMPP**:
   Make sure XAMPP is installed on the computer with **Apache** and **MySQL**.

2. **Copy Project Folder**:
   Paste the extracted `HostelProject` folder inside your XAMPP `htdocs` directory:
   `C:\xampp\htdocs\HostelProject\`

3. **Start XAMPP Control Panel**:
   - Start **Apache**
   - Start **MySQL**

4. **Import Database**:
   - Open browser and go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Click **Databases** -> Create a new database named `hostel_management`
   - Select `hostel_management` database -> Click **Import** tab
   - Choose the `hostel_management.sql` file located in the root of the project folder
   - Click **Import / Go** at the bottom.

5. **Run the Project**:
   Open browser and navigate to:
   👉 **[http://localhost/HostelProject/index.php](http://localhost/HostelProject/index.php)**

---

## 🔑 Default Login Credentials

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| 🛡️ **Admin** | **Rajesh Kumar** | `admin@hostel.com` | `password123` |
| 🔑 **Warden** | **Dr. Ramesh Chandra** | `warden@hostel.com` | `password123` |
| 🎓 **Student** | **Aarav Sharma** | `student@hostel.com` | `password123` |