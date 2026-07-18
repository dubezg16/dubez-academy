<?php
/**
 * Template Name: Notifications
 */

get_header();

if (!is_user_logged_in()) {
    wp_redirect(home_url('/portal-access/'));
    exit;
}

$user_id = get_current_user_id();
global $wpdb;

$per_page = 10;
$current_page = isset($_GET['page_no']) ? max(1, intval($_GET['page_no'])) : 1;
$offset = ($current_page - 1) * $per_page;

// ✅ Fetch messages
$total_messages = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}dubez_messages
         WHERE recipient_id = %d OR role_target = 'all'",
        $user_id
    )
);

$messages = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dubez_messages
         WHERE recipient_id = %d OR role_target = 'all'
         ORDER BY created_at DESC
         LIMIT %d OFFSET %d",
        $user_id,
        $per_page,
        $offset
    )
);

$total_pages = ceil($total_messages / $per_page);

// ✅ Mark all as read
$wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->prefix}dubez_messages
         SET status = 'read'
         WHERE (recipient_id = %d OR role_target = 'all')
         AND status = 'unread'",
        $user_id
    )
);
?>

<section class="notifications-page">
    <div class="container">

        <h1>Notification Center</h1>

        <?php if (!empty($messages)) : ?>
            <ul style="list-style:none; padding-left:0;">
                <?php foreach ($messages as $msg) : ?>
                    <li style="margin-bottom:15px; padding:15px; background:#f3f4f6; border-radius:6px;">
                        <strong><?php echo esc_html($msg->subject); ?></strong><br>
                        <small><?php echo esc_html($msg->created_at); ?></small>
                        <p><?php echo esc_html($msg->message_body); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($total_pages > 1) : ?>
                <div style="margin-top:15px;">
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <a href="?page_no=<?php echo $i; ?>" style="margin-right:10px;">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <p>No notifications available.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>