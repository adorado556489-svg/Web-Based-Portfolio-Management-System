<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "store_db";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Profile Table
$conn->query("CREATE TABLE IF NOT EXISTS Profile_Tbl (
    profile_id   INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100),
    title        VARCHAR(150),
    bio          TEXT,
    avatar_url   VARCHAR(255),
    resume_url   VARCHAR(255),
    email        VARCHAR(100) UNIQUE NOT NULL,
    location     VARCHAR(100),
    is_available BOOLEAN DEFAULT TRUE,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Social Links Table
$conn->query("CREATE TABLE IF NOT EXISTS social_links (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    platform   VARCHAR(50)  NOT NULL,
    url        VARCHAR(255) NOT NULL,
    icon_url   VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Skills Table
$conn->query("CREATE TABLE IF NOT EXISTS skills (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(100) NOT NULL,
    category          VARCHAR(50),
    proficiency_level SMALLINT CHECK (proficiency_level BETWEEN 1 AND 5),
    icon_url          VARCHAR(255),
    sort_order        INT DEFAULT 0,
    is_visible        BOOLEAN DEFAULT TRUE,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Projects Table
$conn->query("CREATE TABLE IF NOT EXISTS projects (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(150) NOT NULL,
    description   TEXT,
    thumbnail_url VARCHAR(255),
    live_url      VARCHAR(255),
    repo_url      VARCHAR(255),
    status        VARCHAR(20) DEFAULT 'completed',
    is_featured   BOOLEAN DEFAULT FALSE,
    is_visible    BOOLEAN DEFAULT TRUE,
    sort_order    INT DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Project Tags Table
$conn->query("CREATE TABLE IF NOT EXISTS project_tags (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    tag        VARCHAR(50) NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)");

// Project Skills Table
$conn->query("CREATE TABLE IF NOT EXISTS project_skills (
    project_id INT NOT NULL,
    skill_id   INT NOT NULL,
    PRIMARY KEY (project_id, skill_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id)   REFERENCES skills(id)   ON DELETE CASCADE
)");

// Contact Messages Table
$conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    sender_name  VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    subject      VARCHAR(200),
    message      TEXT NOT NULL,
    is_read      BOOLEAN DEFAULT FALSE,
    sent_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Seed Profile
$result = $conn->query("SELECT COUNT(*) as count FROM Profile_Tbl");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO Profile_Tbl (full_name, title, bio, email, location, is_available, avatar_url) VALUES ('Arfred A. Dorado', 'Full-Stack Web Developer', 'Hello, my name is Arfred Dorado and I am a second-year IT student at University of Mindanao. Throughout the rest of my college career, I will continue to gain experience through creating mini-projects, vibecoding, research, and co-curricular activities.', 'a.dorado.556489@umindanao.edu.ph', 'Mintal, Davao City, PH', 1, 'uploads/profile/aped.jpg')");
}

// Seed Social Links
$result = $conn->query("SELECT COUNT(*) as count FROM social_links");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO social_links (platform, url, sort_order) VALUES ('GitHub', 'https://github.com/adoredo556489-svg', 1)");
    $conn->query("INSERT INTO social_links (platform, url, sort_order) VALUES ('LinkedIn', 'https://www.linkedin.com/in/arfred-dorado-2b308b330/', 2)");
    $conn->query("INSERT INTO social_links (platform, url, sort_order) VALUES ('Facebook', 'https://www.facebook.com/arfredadorado', 3)");
}

// Seed Skills
$result = $conn->query("SELECT COUNT(*) as count FROM skills");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('HTML', 'Frontend', 5, 1)");
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('CSS', 'Frontend', 5, 2)");
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('JavaScript', 'Frontend', 4, 3)");
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('Python', 'Backend', 3, 4)");
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('PHP', 'Backend', 3, 5)");
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('PostgreSQL', 'Backend', 1, 6)");
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('Git', 'Tools', 4, 7)");
    $conn->query("INSERT INTO skills (name, category, proficiency_level, sort_order) VALUES ('Figma', 'Tools', 4, 8)");
}

// Seed Projects
$result = $conn->query("SELECT COUNT(*) as count FROM projects");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO projects (title, description, live_url, repo_url, status, is_featured, sort_order) VALUES ('SHS Enrollment System', 'A simple Senior High School enrollment system built with PHP and MySQL. Handles student registration, subject assignment, and enrollment records.', '', 'https://github.com/adoredo556489-svg', 'completed', 1, 1)");
    $conn->query("INSERT INTO projects (title, description, live_url, repo_url, status, is_featured, sort_order) VALUES ('UI Prototype (Figma)', 'A UI/UX prototype designed in Figma showcasing modern interface design principles and user-centered design concepts.', 'https://www.figma.com/design/WS8QT70yR7EBAKEDUbtZVT/Designs?node-id=116-114', '', 'completed', 1, 2)");

    $p1 = $conn->query("SELECT id FROM projects WHERE title='SHS Enrollment System' LIMIT 1")->fetch_assoc()['id'];
    $p2 = $conn->query("SELECT id FROM projects WHERE title='UI Prototype (Figma)' LIMIT 1")->fetch_assoc()['id'];

    $conn->query("INSERT INTO project_tags (project_id, tag) VALUES ($p1, 'PHP')");
    $conn->query("INSERT INTO project_tags (project_id, tag) VALUES ($p1, 'MySQL')");
    $conn->query("INSERT INTO project_tags (project_id, tag) VALUES ($p1, 'HTML')");
    $conn->query("INSERT INTO project_tags (project_id, tag) VALUES ($p1, 'CSS')");
    $conn->query("INSERT INTO project_tags (project_id, tag) VALUES ($p2, 'Figma')");
    $conn->query("INSERT INTO project_tags (project_id, tag) VALUES ($p2, 'UI/UX')");
    $conn->query("INSERT INTO project_tags (project_id, tag) VALUES ($p2, 'Prototype')");
}
?>
