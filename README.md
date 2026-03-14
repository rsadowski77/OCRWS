# OCRWS
A simple course registration system coded in PHP.

Quick start
1) Copy folder 'ocrws_php_week5' into XAMPP htdocs, e.g. C:\xampp\htdocs\ocrws_php_week5
2) Start Apache + MySQL in the XAMPP Control Panel
3) Open phpMyAdmin and import: sql/ocrws_week5_schema.sql
4) Update includes/config.php if your MySQL credentials differ
5) Browse to: http://localhost/ocrws_php_week5/index.php

Seeded logins
- Administrator: admin / Admin123!
- Instructor: instructor1 / Instructor123!

Role summary
- Students: self-register, browse offerings, enroll, join waitlists, claim seats, manage their schedules
- Instructors: schedule offerings and view rosters
- Administrators: manage roles, manage courses, manage student schedules, and override capacity by moving students from waitlists into enrollments

Suggested test flow
1) Register 2+ student accounts
2) Enroll students until an offering is full
3) Enroll one more student to create a waitlist entry
4) Drop a student from the course
5) Log in as the first waitlisted student and use Claim Seat
6) Log in as admin and use waitlist_admin.php?offering=<id> to override-enroll a waitlisted student
