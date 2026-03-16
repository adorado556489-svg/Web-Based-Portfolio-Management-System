<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="index.php">⚡ Admin</a>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php"     class="<?= basename($_SERVER['PHP_SELF']) == 'index.php'    ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="profile.php"   class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php'  ? 'active' : '' ?>">👤 Profile</a>
        <a href="projects.php"  class="<?= basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : '' ?>">🚀 Projects</a>
        <a href="skills.php"    class="<?= basename($_SERVER['PHP_SELF']) == 'skills.php'   ? 'active' : '' ?>">🛠️ Skills</a>
        <a href="messages.php"  class="<?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>">📩 Messages</a>
        <a href="../index.php" target="_blank">🌐 View Site</a>
    </nav>
</aside>
