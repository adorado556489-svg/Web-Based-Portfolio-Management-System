<?php require_once '../db.php'; ?>
<?php
$message = '';

// CREATE
if (isset($_POST['add_skill'])) {
    $name  = trim($_POST['name']);
    $cat   = trim($_POST['category']);
    $level = (int)$_POST['proficiency_level'];
    $sort  = (int)$_POST['sort_order'];
    $vis   = isset($_POST['is_visible']) ? 1 : 0;
    $stmt  = $conn->prepare("INSERT INTO skills (name, category, proficiency_level, sort_order, is_visible) VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssiii", $name, $cat, $level, $sort, $vis);
    $message = $stmt->execute()
        ? '<div class="alert success">Skill added!</div>'
        : '<div class="alert error">Error: ' . $conn->error . '</div>';
}

// UPDATE
if (isset($_POST['update_skill'])) {
    $id    = (int)$_POST['skill_id'];
    $name  = trim($_POST['name']);
    $cat   = trim($_POST['category']);
    $level = (int)$_POST['proficiency_level'];
    $sort  = (int)$_POST['sort_order'];
    $vis   = isset($_POST['is_visible']) ? 1 : 0;
    $stmt  = $conn->prepare("UPDATE skills SET name=?, category=?, proficiency_level=?, sort_order=?, is_visible=? WHERE id=?");
    $stmt->bind_param("ssiiii", $name, $cat, $level, $sort, $vis, $id);
    $message = $stmt->execute()
        ? '<div class="alert success">Skill updated!</div>'
        : '<div class="alert error">Error: ' . $conn->error . '</div>';
}

// DELETE
if (isset($_POST['delete_skill'])) {
    $id = (int)$_POST['skill_id'];
    $conn->query("DELETE FROM skills WHERE id=$id");
    $message = '<div class="alert success">Skill deleted!</div>';
}

// Fetch all
$skills = [];
$result = $conn->query("SELECT * FROM skills ORDER BY category, sort_order ASC");
while ($row = $result->fetch_assoc()) $skills[] = $row;

// Edit mode
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($skills as $s) { if ($s['id'] == $eid) { $edit = $s; break; } }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="page-header">
        <h1>Manage Skills</h1>
        <?php if ($edit): ?>
            <a href="skills.php" class="btn btn-outline">+ Add New</a>
        <?php endif; ?>
    </div>

    <?= $message ?>

    <div class="form-card">
        <h2><?= $edit ? 'Edit Skill' : 'Add New Skill' ?></h2>
        <form method="POST">
            <?php if ($edit): ?>
                <input type="hidden" name="skill_id" value="<?= $edit['id'] ?>">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Skill Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <?php foreach (['Frontend','Backend','Tools','Other'] as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($edit['category'] ?? '') == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Proficiency Level (1–5)</label>
                    <input type="number" name="proficiency_level" min="1" max="5" value="<?= $edit['proficiency_level'] ?? 3 ?>">
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="<?= $edit['sort_order'] ?? 0 ?>">
                </div>
            </div>
            <div class="form-group" style="flex-direction:row;align-items:center;gap:0.7rem;">
                <input type="checkbox" name="is_visible" id="skill_visible" <?= ($edit['is_visible'] ?? 1) ? 'checked' : '' ?> style="width:auto;">
                <label for="skill_visible" style="margin:0;">Visible on portfolio</label>
            </div>
            <div style="display:flex;gap:1rem;margin-top:1rem;">
                <button type="submit" name="<?= $edit ? 'update_skill' : 'add_skill' ?>" class="btn btn-primary">
                    <?= $edit ? 'Update Skill' : 'Add Skill' ?>
                </button>
                <?php if ($edit): ?>
                    <a href="skills.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Level</th>
                    <th>Order</th>
                    <th>Visible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($skills as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td><?= htmlspecialchars($s['category']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <div style="width:60px;height:6px;background:var(--border);border-radius:99px;overflow:hidden;">
                                <div style="width:<?= ($s['proficiency_level']/5)*100 ?>%;height:100%;background:var(--blue);border-radius:99px;"></div>
                            </div>
                            <span style="font-size:0.8rem;color:var(--gray);"><?= $s['proficiency_level'] ?>/5</span>
                        </div>
                    </td>
                    <td><?= $s['sort_order'] ?></td>
                    <td><?= $s['is_visible'] ? '✅' : '❌' ?></td>
                    <td>
                        <a href="skills.php?edit=<?= $s['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this skill?')">
                            <input type="hidden" name="skill_id" value="<?= $s['id'] ?>">
                            <button type="submit" name="delete_skill" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($skills)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--gray);padding:2rem;">No skills yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
