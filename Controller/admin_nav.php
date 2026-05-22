<?php
require_once(__DIR__ . "/admin_auth.php");

function admin_nav_h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function admin_nav_link($href, $icon, $label, $activeKey, $currentKey){
    $activeClass = $activeKey === $currentKey ? ' class="active"' : '';
    echo '<a href="' . admin_nav_h($href) . '"' . $activeClass . '><i class="' . admin_nav_h($icon) . '"></i> ' . admin_nav_h($label) . '</a>';
}

function render_admin_sidebar($currentKey = ''){
    $role = admin_current_role();
    ?>
    <div class="side-menu" id="menu">
        <i class="fas fa-times close-btn" onclick="toggleMenu()"></i>
        <?php if ($role === 'admin'): ?>
        <?php admin_nav_link('dashboard.php', 'fas fa-chart-line', 'Dashboard', 'dashboard', $currentKey); ?>
        <?php admin_nav_link('personal-order.php', 'fas fa-box', 'Orders', 'orders', $currentKey); ?>
        <?php admin_nav_link('escalated-messages.php', 'fas fa-envelope', 'Messages', 'escalated-messages', $currentKey); ?>
        <?php admin_nav_link('new-item.php', 'fas fa-plus', 'New Item', 'new-item', $currentKey); ?>
        <?php admin_nav_link('manage-items.php', 'fas fa-cog', 'Manage Items', 'manage-items', $currentKey); ?>
        <?php admin_nav_link('report.php', 'fas fa-file-export', 'Export Report', 'report', $currentKey); ?>
        <?php admin_nav_link('saless.php', 'fas fa-sitemap', 'System Overview', 'system-overview', $currentKey); ?>
        <?php elseif ($role === 'staff'): ?>
            <?php admin_nav_link('dashboard.php', 'fas fa-gauge', 'Staff Panel', 'dashboard', $currentKey); ?>
            <?php admin_nav_link('personal-order.php', 'fas fa-box', 'Orders', 'orders', $currentKey); ?>
            <?php admin_nav_link('messages.php', 'fas fa-envelope', 'Messages', 'messages', $currentKey); ?>
        <?php endif; ?>
        <a href="../../Controller/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <?php
}
?>
