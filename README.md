# Dubez Academy ERP Platform

Welcome to the **Dubez Academy** repository. This is an elite School ERP and custom portal system built on top of WordPress, featuring a custom theme and management plugin to power academic tracking, portal access, and school administration.

## 📋 Table of Contents
1. [Project Structure](#-project-structure)
2. [Key Components](#-key-components)
3. [Local Development Setup](#%EF%B8%8F-local-development-setup)
4. [How to View/Access the Live Site](#-how-to-viewaccess-the-live-site)
5. [Generating Documentation](#-generating-documentation)

---

## 📂 Project Structure

This project is set up for local development using **Local** (by WP Engine):

- **`app/`**: Contains the WordPress codebase.
  - **`app/public/wp-content/themes/dubez-academy-theme/`**: The core custom theme containing responsive templates, student portal, teacher dashboard, and parent portal layouts.
  - **`app/public/wp-content/plugins/dubez-academy-management/`**: Custom backend functions, academic algorithms, ranking modules, and database controllers.
- **`doc-gen/`**: Automated script system using Node.js and the `docx` library to programmatically generate 30+ page formal PDFs/Docs for the project documentation.
- **`conf/`** & **`logs/`**: Server configuration and logs (configured locally; ignored in version control).

---

## ✨ Key Components

### 🎨 Custom Theme (`dubez-academy-theme`)
- **Responsive Portals**: Dedicated student, teacher, and parent portal interfaces.
- **Micro-interactions & UX**: Styled with elegant vanilla CSS using a modern HSL-tailored palette (gold/blue themes) and seamless dark/light mode toggle.
- **Branding**: Implements Playfair Display and Inter Google font typography.

### ⚙️ Custom Plugin (`dubez-academy-management`)
- **Database Engine**: Manages custom school tables, academic results, and billing entities.
- **Ranking Engine**: Custom algorithms for student GPA and level ranking.
- **Authentication**: Role-based access control (Teacher, Student, Parent, Admin).

---

## 🛠️ Local Development Setup

To run this website locally on your computer:

1. **Install Local**: Download and install [LocalWP](https://localwp.com/).
2. **Import Site**:
   - Create a new site in Local: "Dubez Academy".
   - Replace the site's `app/` folder with the `app/` folder in this repository.
   - Import the database SQL dump (located at `app/sql/` if backed up, or clone from Local database).
3. **Start Site**: Click **Start Site** in LocalWP to load the site at `http://dubez-academy.local`.

---

## 🌐 How to View/Access the Live Site

Because this site is built using WordPress (a dynamic PHP and SQL backend), it cannot be hosted statically using GitHub Pages directly. 

To view and access your site over the internet:
1. Open **Local WP** on your computer.
2. Select **Dubez Academy**.
3. Under the site's overview tab, click **Enable Live Link**.
4. This will generate a public URL (e.g., `https://dubez-academy.local.lt/` or via Ngrok) which you can share and use to access the website from any browser or dev device.

---

## 📄 Generating Documentation
To regenerate the ERP project reports:
1. Navigate to the `doc-gen/` directory:
   ```bash
   cd doc-gen
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Run the generator script:
   ```bash
   node generate_report.js
   ```
