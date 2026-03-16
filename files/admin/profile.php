<?php require_once '../db.php'; ?>
<?php
$message = '';
$profile = $conn->query("SELECT * FROM Profile_Tbl LIMIT 1")->fetch_assoc();

// UPDATE PROFILE
if (isset($_POST['update_profile'])) {
    $full_name    = trim($_POST['full_name']);
    $title        = trim($_POST['title']);
    $bio          = trim($_POST['bio']);
    $email        = trim($_POST['email']);
    $location     = trim($_POST['location']);
    $resume_url   = trim($_POST['resume_url']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $avatar_url   = $profile['avatar_url'];

    // Handle avatar upload
    if (!empty($_FILES['avatar']['name'])) {
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['avatar']['type'];
        if (in_array($fileType, $allowed)) {
            $uploadDir = '../uploads/profile/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName   = time() . '_' . basename($_FILES['avatar']['name']);
            $filePath   = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filePath)) {
                $avatar_url = 'uploads/profile/' . $fileName;
            }
        } else {
            $message = '<div class="alert error">Only JPG, PNG, GIF, WEBP allowed.</div>';
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE Profile_Tbl SET full_name=?, title=?, bio=?, email=?, location=?, resume_url=?, is_available=?, avatar_url=? WHERE profile_id=?");
        $stmt->bind_param("ssssssiis", $full_name, $title, $bio, $email, $location, $resume_url, $is_available, $avatar_url, $profile['profile_id']);
        if ($stmt->execute()) {
            $message = '<div class="alert success">Profile updated successfully!</div>';
            $profile  = $conn->query("SELECT * FROM Profile_Tbl LIMIT 1")->fetch_assoc();
        } else {
            $message = '<div class="alert error">Update failed: ' . $conn->error . '</div>';
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
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="page-header">
        <h1>Edit Profile</h1>
    </div>

    <?= $message ?>

    <div class="form-card">
        <h2>Your Information</h2>
        <form method="POST" enctype="multipart/form-data">
            <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom:1.5rem;">
                <?php if ($profile['avatar_url']): ?>
                    <img src="../<?= htmlspecialchars($profile['avatar_url']) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--blue-light);">
                <?php else: ?>
                    <div style="width:80px;height:80px;border-radius:50%;background:var(--blue-light);display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--blue);">
                        <?= strtoupper(substr($profile['full_name'] ?? 'A', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="form-group" style="flex:1;margin:0;">
                    <label>Profile Picture</label>
                    <input type="file" name="avatar" accept="image/*">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($profile['title'] ?? '') ?>" placeholder="e.g. Full-Stack Developer">
                </div>
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="4"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($profile['location'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Resume URL</label>
                <input type="url" name="resume_url" value="<?= htmlspecialchars($profile['resume_url'] ?? '') ?>" placeholder="https://drive.google.com/...">
            </div>
            <div class="form-group" style="flex-direction:row;align-items:center;gap:0.7rem;">
                <input type="checkbox" name="is_available" id="is_available" <?= $profile['is_available'] ? 'checked' : '' ?> style="width:auto;">
                <label for="is_available" style="margin:0;">Available for work</label>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary" style="margin-top:1rem;">Save Changes</button>
        </form>
    </div>
</main>

</body>
</html>
