# Travel website — PHP + MySQL + Admin Panel

This project follows the supplied wireframe:
- About us: large hero/content block
- Tours: 5 cards
- Feedbacks / our reviews: review cards
- Admin panel for text + image management

## Quick start with Docker
1. Install Docker Desktop.
2. In this folder run:
   docker compose up -d --build
3. Open:
   http://localhost:8088
4. Admin:
   http://localhost:8088/admin/login.php
   Username: admin
   Password: admin123

Change the admin password immediately after first login.

## XAMPP / Laragon
1. Put the folder into htdocs/www.
2. Create a MySQL database called `travel_site`.
3. Edit `includes/config.php` with your DB credentials.
4. Open `/install.php` once.
5. Delete or rename `install.php` after installation.
6. Admin: `/admin/login.php` (admin / admin123).

## What the admin can manage
- About section: title, text, button and hero image
- Tours: create/edit/delete title, description, price, image and ordering
- Reviews: create/edit/delete reviewer, text, rating and image
- Site settings: logo/name, phone, email, social links
- Image uploads (JPG/PNG/WebP, up to 100 MB in Docker)

The public site is intentionally clean and neutral so you can replace the content/images with your own travel brand material.
