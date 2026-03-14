CREATE DATABASE IF NOT EXISTS ocrws CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ocrws;
DROP TABLE IF EXISTS waitlist_entries;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS course_offerings;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS semesters;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Student', 'Instructor', 'Administrator') NOT NULL DEFAULT 'Student',
  created_at DATETIME NOT NULL,
  UNIQUE KEY uq_users_user_id (user_id)
) ENGINE=InnoDB;
CREATE TABLE profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_pk INT NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uq_profiles_email (email),
  CONSTRAINT fk_profiles_users FOREIGN KEY (user_pk) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE semesters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  term VARCHAR(10) NOT NULL,
  term_order INT NOT NULL,
  year INT NOT NULL,
  UNIQUE KEY uq_semester (term, year)
) ENGINE=InnoDB;
CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_code VARCHAR(20) NOT NULL,
  title VARCHAR(255) NOT NULL,
  capacity INT NOT NULL,
  UNIQUE KEY uq_course_code (course_code)
) ENGINE=InnoDB;
CREATE TABLE course_offerings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  semester_pk INT NOT NULL,
  course_pk INT NOT NULL,
  instructor_pk INT NOT NULL,
  UNIQUE KEY uq_offering (semester_pk, course_pk, instructor_pk),
  CONSTRAINT fk_offering_semester FOREIGN KEY (semester_pk) REFERENCES semesters(id) ON DELETE CASCADE,
  CONSTRAINT fk_offering_course FOREIGN KEY (course_pk) REFERENCES courses(id) ON DELETE CASCADE,
  CONSTRAINT fk_offering_instructor FOREIGN KEY (instructor_pk) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_pk INT NOT NULL,
  offering_pk INT NOT NULL,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uq_enrollment (user_pk, offering_pk),
  CONSTRAINT fk_enroll_user FOREIGN KEY (user_pk) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_enroll_offering FOREIGN KEY (offering_pk) REFERENCES course_offerings(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE waitlist_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_pk INT NOT NULL,
  offering_pk INT NOT NULL,
  position INT NOT NULL,
  status ENUM('Waiting', 'Enrolled', 'Removed') NOT NULL DEFAULT 'Waiting',
  created_at DATETIME NOT NULL,
  UNIQUE KEY uq_waitlist_active (user_pk, offering_pk, status),
  CONSTRAINT fk_waitlist_user FOREIGN KEY (user_pk) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_waitlist_offering FOREIGN KEY (offering_pk) REFERENCES course_offerings(id) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT INTO users (user_id, password_hash, role, created_at) VALUES
('admin', '$2y$10$X4v2J5a7TElTT3e9gqv8v.9ZP0OWcZH/5LiCFYI8aAkaI0s5momk6', 'Administrator', NOW()),
('instructor1', '$2y$10$8x/8bVsgcuyo6edSxnl2lO0e6QF6UVx/FuWRzE7dDzIvmhjYQdkCe', 'Instructor', NOW());
INSERT INTO profiles (user_pk, full_name, email, phone, created_at)
SELECT id, 'System Administrator', 'admin@example.com', '555-1000', NOW() FROM users WHERE user_id = 'admin';
INSERT INTO profiles (user_pk, full_name, email, phone, created_at)
SELECT id, 'Primary Instructor', 'instructor1@example.com', '555-2000', NOW() FROM users WHERE user_id = 'instructor1';
INSERT INTO semesters (term, term_order, year) VALUES
('Spring', 1, 2026), ('Summer', 2, 2026), ('Fall', 3, 2026);
INSERT INTO courses (course_code, title, capacity) VALUES
('SE101', 'Introduction to Software Engineering', 2),
('SE220', 'Software Requirements', 2),
('SE310', 'Software Design and UML', 2),
('SE401', 'Software Testing and Quality Assurance', 2);
INSERT INTO course_offerings (semester_pk, course_pk, instructor_pk)
SELECT s.id, c.id, u.id
FROM semesters s
JOIN courses c
JOIN users u ON u.user_id = 'instructor1'
WHERE s.term = 'Spring' AND s.year = 2026
  AND c.course_code IN ('SE101', 'SE220', 'SE310');
