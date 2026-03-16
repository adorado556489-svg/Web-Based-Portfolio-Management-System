<?php require_once 'db.php'; ?>
<?php
// Fetch profile
$profile = $conn->query("SELECT * FROM Profile_Tbl LIMIT 1")->fetch_assoc();

// Fetch social links
$social_links = $conn->query("SELECT * FROM social_links WHERE is_visible = TRUE ORDER BY sort_order ASC");

// Fetch skills grouped by category
$skills_result = $conn->query("SELECT * FROM skills WHERE is_visible = TRUE ORDER BY category, sort_order ASC");
$skills_by_category = [];
while ($skill = $skills_result->fetch_assoc()) {
    $skills_by_category[$skill['category']][] = $skill;
}

// Fetch projects
$projects_result = $conn->query("
    SELECT p.*, GROUP_CONCAT(pt.tag ORDER BY pt.id SEPARATOR ',') as tags
    FROM projects p
    LEFT JOIN project_tags pt ON p.id = pt.project_id
    WHERE p.is_visible = TRUE
    GROUP BY p.id
    ORDER BY p.sort_order ASC
");

// Handle contact form submission
$contact_success = false;
$contact_error   = '';
if (isset($_POST['send_message'])) {
    $name    = trim($_POST['sender_name']);
    $email   = trim($_POST['sender_email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $contact_error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (sender_name, sender_email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            $contact_success = true;
        } else {
            $contact_error = 'Something went wrong. Please try again.';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile['full_name'] ?? 'Portfolio') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<!-- NAV -->
<nav class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-logo"><?= strtoupper(explode(' ', $profile['full_name'] ?? 'Portfolio')[0]) ?></a>
        <ul class="nav-links">
            <li><a href="#about">About</a></li>
            <li><a href="#skills">Skills</a></li>
            <li><a href="#projects">Projects</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <a href="admin/index.php" class="nav-admin">Admin ↗</a>
        <button class="nav-toggle" id="navToggle">&#9776;</button>
    </div>
</nav>

<!-- HERO -->
<section class="hero" id="about">
    <div class="hero-inner">
        <div class="hero-text">
            <span class="hero-tag">
                <?php if ($profile['is_available']): ?>
                    <span class="dot green"></span> Available for work
                <?php else: ?>
                    <span class="dot red"></span> Not available
                <?php endif; ?>
            </span>
            <h1 class="hero-name"><?= htmlspecialchars($profile['full_name'] ?? '') ?></h1>
            <h2 class="hero-title"><?= htmlspecialchars($profile['title'] ?? '') ?></h2>
            <p class="hero-bio"><?= htmlspecialchars($profile['bio'] ?? '') ?></p>
            <p class="hero-location">📍 <?= htmlspecialchars($profile['location'] ?? '') ?></p>
            <div class="hero-actions">
                <?php if ($profile['resume_url']): ?>
                    <a href="<?= htmlspecialchars($profile['resume_url']) ?>" class="btn btn-primary" target="_blank">Download CV</a>
                <?php endif; ?>
                <a href="#contact" class="btn btn-outline">Get in Touch</a>
            </div>
            <div class="hero-socials">
                <?php while ($link = $social_links->fetch_assoc()): ?>
                    <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="social-link">
                        <?= htmlspecialchars($link['platform']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="hero-image">
            <?php if ($profile['avatar_url']): ?>
                <img src="<?= htmlspecialchars($profile['avatar_url']) ?>" alt="<?= htmlspecialchars($profile['full_name']) ?>">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($profile['full_name'] ?? 'A', 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SKILLS -->
<section class="skills-section" id="skills">
    <div class="container">
        <h2 class="section-title">Skills</h2>
        <p class="section-sub">Technologies I work with</p>
        <?php foreach ($skills_by_category as $category => $skills): ?>
            <div class="skill-group">
                <h3 class="skill-category"><?= htmlspecialchars($category) ?></h3>
                <div class="skill-cards">
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-card">
                            <span class="skill-name"><?= htmlspecialchars($skill['name']) ?></span>
                            <div class="skill-bar">
                                <div class="skill-fill" style="width: <?= ($skill['proficiency_level'] / 5) * 100 ?>%"></div>
                            </div>
                            <span class="skill-level"><?= $skill['proficiency_level'] ?>/5</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PROJECTS -->
<section class="projects-section" id="projects">
    <div class="container">
        <h2 class="section-title">Projects</h2>
        <p class="section-sub">Things I've built...so far.</p>
        <div class="projects-grid">
            <?php while ($project = $projects_result->fetch_assoc()): ?>
                <div class="project-card <?= $project['is_featured'] ? 'featured' : '' ?>">
                    <?php if ($project['thumbnail_url']): ?>
                        <div class="project-thumb">
                            <img src="<?= htmlspecialchars($project['thumbnail_url']) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        </div>
                    <?php else: ?>
                        <div class="project-thumb-placeholder">
                            <span><?= strtoupper(substr($project['title'], 0, 2)) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="project-body">
                        <div class="project-header">
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <span class="project-status <?= $project['status'] ?>"><?= ucfirst($project['status']) ?></span>
                        </div>
                        <p><?= htmlspecialchars($project['description']) ?></p>
                        <?php if ($project['tags']): ?>
                            <div class="project-tags">
                                <?php foreach (explode(',', $project['tags']) as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="project-links">
                            <?php if ($project['repo_url']): ?>
                                <a href="<?= htmlspecialchars($project['repo_url']) ?>" target="_blank" class="btn btn-sm">GitHub ↗</a>
                            <?php endif; ?>
                            <?php if ($project['live_url']): ?>
                                <a href="<?= htmlspecialchars($project['live_url']) ?>" target="_blank" class="btn btn-sm btn-outline">Live Demo ↗</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section class="contact-section" id="contact">
    <div class="container">
        <h2 class="section-title">Contact</h2>
        <p class="section-sub">Let's work together</p>
        <div class="contact-inner">
            <div class="contact-info">
                <p>Feel free to reach out for collaborations, questions, or just to say hello!</p>
                <p class="contact-email">📧 <?= htmlspecialchars($profile['email'] ?? '') ?></p>
                <p class="contact-location">📍 <?= htmlspecialchars($profile['location'] ?? '') ?></p>
            </div>
            <form class="contact-form" method="POST" action="#contact">
                <?php if ($contact_success): ?>
                    <div class="alert success">Message sent successfully!</div>
                <?php endif; ?>
                <?php if ($contact_error): ?>
                    <div class="alert error"><?= htmlspecialchars($contact_error) ?></div>
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="sender_name" placeholder="Your name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="sender_email" placeholder="your@email.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="What's this about?">
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" rows="5" placeholder="Your message..." required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <p>© <?= date('Y') ?> <?= htmlspecialchars($profile['full_name'] ?? '') ?>. Built with PHP & ❤️</p>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
