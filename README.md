# 🏠 Design Inside

> A modern full-stack interior design website for showcasing services, projects, and managing customer inquiries.

Design Inside is an interior design business website developed using **HTML, CSS, JavaScript, PHP, and MySQL**. The platform enables visitors to explore interior design services, browse completed projects, request quotations, and allows administrators to manage customer leads through a dedicated admin panel.

---

## 📄 Website Pages

| Page Name | File |
|------------|------|
| Home | `index.html` |
| Services | `service.html` |
| Projects | `project.html` |
| About Us | `about_us.html` |
| Quotation Form | `quotation_form.php` |

---

## 💻 Technology Stack

| Layer | Technology |
|--------|------------|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend | PHP |
| Database | MySQL |
| Local Server | Apache (XAMPP) |

---

## 🚀 Key Features

- Interactive hero section with autoplay background video
- Responsive image gallery featuring previous/next navigation
- Interior design service showcase
  - Kitchen Remodeling
  - Bathroom Remodeling
  - Home Additions
- Animated statistics section
  - Current Projects
  - Homes Renovated
  - Valued Partners
- Customer quotation request form
- Responsive navigation bar with hamburger menu
- Complete admin dashboard including:
  - Secure admin login
  - Customer inquiry management
  - Dashboard overview
  - Completed projects management

---

## 🗃️ Database Information

The application uses the following database tables:

| Table | Purpose |
|-------|---------|
| **Admins** | Stores administrator login credentials and security questions |
| **Customer** | Stores quotation requests submitted by clients |
| **Compro** | Stores completed project details along with profit information |

**SQL Setup File**

```
full_admin/database.txt
```

---

## ⚙️ Installation Guide

### Prerequisites

- XAMPP (Apache, PHP, and MySQL)

### Installation Steps

#### 1. Copy the project

Place the project inside the XAMPP `htdocs` directory.

```text
C:\xampp\htdocs\Design_inside\
```

#### 2. Start the server

Launch **Apache** and **MySQL** using the XAMPP Control Panel.

#### 3. Import the database

Open **phpMyAdmin** and execute the SQL script located at:

```text
full_admin/database.txt
```

#### 4. Run the website

Open your browser and visit:

```text
http://localhost/Design_inside/
```

#### 5. Open the Admin Panel

```text
http://localhost/Design_inside/full_admin/
```

---

## 📂 Project Directory

```text
Design_inside/
│
├── Assets/
│   ├── Images/
│   └── Videos/
│
├── full_admin/
│   ├── admin/
│   └── database.txt
│
├── index.html
├── service.html
├── project.html
├── about_us.html
├── quotation_form.php
├── script.js
└── *.css
```

---

## 👨‍💻 Development Team

**Tanmay Bhogekar**  
**Kabir Bundele**
