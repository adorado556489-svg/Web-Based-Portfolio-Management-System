<?php require_once '../db.php'; ?>
<?php
$message = '';

// CREATE
if (isset($_POST['add_project'])) {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $live_url    = trim($_POST['live_url']);
    $repo_url    = trim($_POST['repo_url']);
    $status      = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_visible  = isset($_POST['is_visible'])  ? 1 : 0;
    $sort_order  = (int)$_POST['sort_order'];
    $tags        = trim($_POST['tags']);
    $thumb       = '';

    if (!empty($_FILES['thumbnail']['name'])) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($_FILES['thumbnail']['type'], $allowed)) {
            $dir = '../uploads/projects/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $fname = time() . '_' . basename($_FILES['thumbnail']['name']);
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dir . $fname)) {
                $thumb = 'uploads/projects/' . $fname;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO projects (title, description, thumbnail_url, live_url, repo_url, status, is_featured, is_visible, sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssii", $title, $description, $thumb, $live_url, $repo_url, $status, $is_featured, $is_visible, $sort_order);
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        foreach (explode(',', $tags) as $tag) {
            $tag = trim($tag);
            if ($tag) {
                $ts = $conn->prepare("INSERT INTO project_tags (project_id, tag) VALUES (?,?)");
                $ts->bind_param("is", $new_id, $tag);
                $ts->execute();
            }
        }
        $message = '<div class="alert success">Project added!</div>';
    } else {
        $message = '<div class="alert error">Error: ' . $conn->error . '</div>';
    }
}

// UPDATE
if (isset($_POST['update_project'])) {
    $id          = (int)$_POST['project_id'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $live_url    = trim($_POST['live_url']);
    $repo_url    = trim($_POST['repo_url']);
    $status      = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_visible  = isset($_POST['is_visible'])  ? 1 : 0;
    $sort_order  = (int)$_POST['sort_order'];
    $tags        = trim($_POST['tags']);

    $existing = $conn->query("SELECT thumbnail_url FROM projects WHERE id=$id")->fetch_assoc();
    $thumb    = $existing['thumbnail_url'];

    if (!empty($_FILES['thumbnail']['name'])) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($_FILES['thumbnail']['type'], $allowed)) {
            $dir = '../uploads/projects/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $fname = time() . '_' . basename($_FILES['thumbnail']['name']);
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dir . $fname)) {
                $thumb = 'uploads/projects/' . $fname;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, thumbnail_url=?, live_url=?, repo_url=?, status=?, is_featured=?, is_visible=?, sort_order=? WHERE id=?");
    $stmt->bind_param("sssssssiii", $title, $description, $thumb, $live_url, $repo_url, $status, $is_featured, $is_visible, $sort_order, $id);
    if ($stmt->execute()) {
        $conn->query("DELETE FROM project_tags WHERE project_id=$id");
        foreach (explode(',', $tags) as $tag) {
            $tag = trim($tag);
            if ($tag) {
                $ts = $conn->prepare("INSERT INTO project_tags (project_id, tag) VALUES (?,?)");
                $ts->bind_param("is", $id, $tag);
                $ts->execute();
            }
        }
        $message = '<div class="alert success">Project updated!</div>';
    } else {
        $message = '<div class="alert error">Error: ' . $conn->error . '</div>';
    }
}

// DELETE
if (isset($_POST['delete_project'])) {
    $id = (int)$_POST['project_id'];
    $conn->query("DELETE FROM projects WHERE id=$id");
    $message = '<div class="alert success">Project deleted!</div>';
}

// Fetch all projects with tags
$projects = [];
$result   = $conn->query("SELECT p.*, GROUP_CONCAT(pt.tag ORDER BY pt.id SEPARATOR ', ') as tags FROM projects p LEFT JOIN project_tags pt ON p.id = pt.project_id GROUP BY p.id ORDER BY p.sort_order ASC");
while ($row = $result->fetch_assoc()) $projects[] = $row;

// Edit mode
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($projects as $p) { if ($p['id'] == $eid) { $edit = $p; break; } }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="page-header">
        <h1><?= $edit ? 'Edit Project' : 'Manage Projects' ?></h1>
        <?php if ($edit): ?>
            <a href="projects.php" class="btn btn-outline">+ Add New</a>
        <?php endif; ?>
    </div>

    <?= $message ?>

    <!-- FORM: Add / Edit -->
    <div class="form-card">
        <h2><?= $edit ? 'Edit: ' . htmlspecialchars($edit['title']) : 'Add New Project' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit): ?>
                <input type="hidden" name="project_id" value="<?= $edit['id'] ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['completed','in-progress','archived'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($edit['status'] ?? 'completed') == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Live URL</label>
                    <input type="url" name="live_url" value="<?= htmlspecialchars($edit['live_url'] ?? '') ?>" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>GitHub / Repo URL</label>
                    <input type="url" name="repo_url" value="<?= htmlspecialchars($edit['repo_url'] ?? '') ?>" placeholder="https://github.com/...">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Thumbnail Image</label>
                    <input type="file" name="thumbnail" accept="image/*">
                    <?php if (!empty($edit['thumbnail_url'])): ?>
                        <small style="color:var(--gray);">Current: <?= htmlspecialchars($edit['thumbnail_url']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags" value="<?= htmlspecialchars($edit['tags'] ?? '') ?>" placeholder="PHP, MySQL, CSS">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="<?= $edit['sort_order'] ?? 0 ?>" min="0">
                </div>
                <div class="form-group" style="flex-direction:row;align-items:center;gap:1.5rem;padding-top:1.5rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="is_featured" <?= ($edit['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        Featured
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="is_visible" <?= ($edit['is_visible'] ?? 1) ? 'checked' : '' ?>>
                        Visible
                    </label>
                </div>
            </div>

            <div style="display:flex;gap:1rem;margin-top:1rem;">
                <button type="submit" name="<?= $edit ? 'update_project' : 'add_project' ?>" class="btn btn-primary">
                    <?= $edit ? 'Update Project' : 'Add Project' ?>
                </button>
                <?php if ($edit): ?>
                    <a href="projects.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Tags</th>
                    <th>Featured</th>
                    <th>Visible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                    <td><span class="project-status <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td style="font-size:0.8rem;color:var(--gray);"><?= htmlspecialchars($p['tags'] ?? '—') ?></td>
                    <td><?= $p['is_featured'] ? '⭐' : '—' ?></td>
                    <td><?= $p['is_visible']  ? '✅' : '❌' ?></td>
                    <td>
                        <a href="projects.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this project?')">
                            <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="delete_project" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--gray);padding:2rem;">No projects yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
