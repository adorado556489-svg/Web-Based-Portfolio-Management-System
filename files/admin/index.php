<?php require_once '../db.php'; ?>
<?php
// Count stats
$total_projects  = $conn->query("SELECT COUNT(*) as c FROM projects")->fetch_assoc()['c'];
$total_skills    = $conn->query("SELECT COUNT(*) as c FROM skills")->fetch_assoc()['c'];
$total_messages  = $conn->query("SELECT COUNT(*) as c FROM contact_messages")->fetch_assoc()['c'];
$unread_messages = $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read = FALSE")->fetch_assoc()['c'];
$profile         = $conn->query("SELECT full_name FROM Profile_Tbl LIMIT 1")->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="page-header">
        <h1>Dashboard</h1>
        <a href="../index.php" class="btn btn-outline" target="_blank">View Portfolio ↗</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">🚀</div>
            <div class="stat-info">
                <span class="stat-number"><?= $total_projects ?></span>
                <span class="stat-label">Projects</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">🛠️</div>
            <div class="stat-info">
                <span class="stat-number"><?= $total_skills ?></span>
                <span class="stat-label">Skills</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">📩</div>
            <div class="stat-info">
                <span class="stat-number"><?= $total_messages ?></span>
                <span class="stat-label">Messages</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">🔔</div>
            <div class="stat-info">
                <span class="stat-number"><?= $unread_messages ?></span>
                <span class="stat-label">Unread</span>
            </div>
        </div>
    </div>

    <div class="quick-links card">
        <h2 style="margin-bottom:1.2rem; font-family:'DM Serif Display',serif;">Quick Actions</h2>
        <div class="quick-grid">
            <a href="profile.php" class="quick-btn">✏️ Edit Profile</a>
            <a href="projects.php" class="quick-btn">🚀 Manage Projects</a>
            <a href="skills.php" class="quick-btn">🛠️ Manage Skills</a>
            <a href="messages.php" class="quick-btn">📩 View Messages <?= $unread_messages > 0 ? "<span class='badge'>$unread_messages</span>" : '' ?></a>
        </div>
    </div>
</main>

</body>
</html>
