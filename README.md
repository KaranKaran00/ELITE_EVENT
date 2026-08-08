# 🎫 Elite Event – Event Management System

A web-based event management system where students can discover events and register using Google Forms. Teachers and administrators can manage events, users, and registrations.

## 📊 Flow Chart

![Elite Event Flow Chart](FLOWCHART.png)

## ✨ Features

### 👨‍🎓 Student
- Register and log in
- Browse and view events
- Register for events using Google Forms
- View **My Registrations**
- One account can register **only once for the same event**

### 👨‍🏫 Teacher
- Create, edit, and delete events
- Add Google Form URL to events
- View event registrations

### 👨‍💼 Admin
- Manage users and user roles
- Manage events
- Manage registrations
- Search and filter registrations

## 📝 Google Form Integration

Each event can have its own Google Form.

```text
Login
  ↓
Browse Events
  ↓
Event Details
  ↓
Register with My Account
  ↓
Check Registration
  ↓
Google Form
  ↓
Submit Form
  ↓
Registration Saved in MySQL
  ↓
My Registrations
```

Google Forms can collect additional event-specific information, with responses available in Google Sheets.

## 🔐 One Registration Per Event

The system prevents duplicate registration for the same event.

MySQL uses:

```sql
UNIQUE (event_id, user_id)
```

**One User + One Event = One Registration**

## 🗄️ MySQL Database

Main tables:

| Table | Purpose |
|---|---|
| `users` | Student, teacher, and admin accounts |
| `events` | Event information and Google Form URLs |
| `registrations` | Event registration records |
| `instagram_posts` | Instagram post information |

### Database Relationship

![Elite Event Flow Chart](DatabaseRelationshipDiagram.png)

## 🛠️ Technologies

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)
![Google Forms](https://img.shields.io/badge/Google%20Forms-7248B9?style=for-the-badge&logo=googleforms&logoColor=white)
![Google Sheets](https://img.shields.io/badge/Google%20Sheets-34A853?style=for-the-badge&logo=googlesheets&logoColor=white)

## 📁 Project Structure

```text
elite_event/
│
├── admin/
├── teacher/
├── config/
│   └── database.php
├── database/
│   └── elite_event_mysql.sql
├── css/
│   └── style.css
├── includes/
├── index.php
├── login.php
├── signup.php
├── events.php
├── event-detail.php
├── create-event.php
├── registrations.php
└── README.md
```

## 👥 User Roles

| Role | Access |
|---|---|
| Student | Browse events, register, view registrations |
| Teacher | Manage events and view registrations |
| Admin | Manage users, events, and registrations |
