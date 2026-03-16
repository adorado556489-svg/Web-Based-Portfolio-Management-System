<?php require_once '../db.php'; ?>
<?php
$message_alert = '';

// Mark as read
if (isset($_POST['mark_read'])) {
    $id = (int)$_POST['message_id'];
    $conn->query("UPDATE contact_messages SET is_read = TRUE WHERE id = $id");
    $message_alert = '<div class="alert success">Marked as read.</div>';
}

// Mark as unread
if (isset($_POST['mark_unread'])) {
    $id = (int)$_POST['message_id'];
    $conn->query("UPDATE contact_messages SET is_read = FALSE WHERE id = $id");
    $message_alert = '<div class="alert success">Marked as unread.</div>';
}

// Delete
if (isset($_POST['delete_message'])) {
    $id = (int)$_POST['message_id'];
    $conn->query("DELETE FROM contact_messages WHERE id = $id");
    $message_alert = '<div class="alert success">Message deleted.</div>';
}

// Fetch all messages
$filter  = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where   = $filter === 'unread' ? 'WHERE is_read = FALSE' : '';
$messages = [];
$result   = $conn->query("SELECT * FROM contact_messages $where ORDER BY sent_at DESC");
while ($row = $result->fetch_assoc()) $messages[] = $row;

$total_unread = $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read = FALSE")->fetch_assoc()['c'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">
    <div class="page-header">
        <h1>Messages <?= $total_unread > 0 ? "<span class='badge'>$total_unread unread</span>" : '' ?></h1>
        <div style="display:flex;gap:0.5rem;">
            <a href="messages.php" class="btn btn-sm <?= $filter !== 'unread' ? 'btn-primary' : 'btn-outline' ?>">All</a>
            <a href="messages.php?filter=unread" class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-outline' ?>">Unread</a>
        </div>
    </div>

    <?= $message_alert ?>

    <?php if (empty($messages)): ?>
        <div class="card" style="text-align:center;padding:3rem;color:var(--gray);">
            📭 No messages <?= $filter === 'unread' ? 'unread' : 'yet' ?>.
        </div>
    <?php endif; ?>

    <?php foreach ($messages as $msg): ?>
        <div class="message-card <?= !$msg['is_read'] ? 'unread' : '' ?>">
            <div class="message-meta">
                <div>
                    <div class="message-sender">
                        <?= htmlspecialchars($msg['sender_name']) ?>
                        <?= !$msg['is_read'] ? '<span class="badge">New</span>' : '' ?>
                    </div>
                    <div class="message-email">📧 <?= htmlspecialchars($msg['sender_email']) ?></div>
                </div>
                <div class="message-date"><?= date('M d, Y g:i A', strtotime($msg['sent_at'])) ?></div>
            </div>
            <?php if ($msg['subject']): ?>
                <div class="message-subject">Subject: <?= htmlspecialchars($msg['subject']) ?></div>
            <?php endif; ?>
            <div class="message-body"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
            <div class="message-actions">
                <a href="mailto:<?= htmlspecialchars($msg['sender_email']) ?>?subject=Re: <?= htmlspecialchars($msg['subject']) ?>" class="btn btn-sm btn-primary">Reply</a>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                    <?php if (!$msg['is_read']): ?>
                        <button type="submit" name="mark_read" class="btn btn-sm btn-outline">Mark as Read</button>
                    <?php else: ?>
                        <button type="submit" name="mark_unread" class="btn btn-sm btn-outline">Mark as Unread</button>
                    <?php endif; ?>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?')">
                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                    <button type="submit" name="delete_message" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</main>

</body>
</html>
