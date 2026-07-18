
<?php
/**
 * Template Name: Teacher Portal
 */

get_header();

if (!current_user_can('teacher')) {
    wp_redirect(home_url('/portal-access/'));
    exit;
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
?>

<section class="teacher-dashboard">
    <button class="mode-toggle"></button>
    <div class="container">

        <div class="teacher-header-block">
            <div>
                <h1>Teacher Dashboard</h1>
                <p class="teacher-subtitle">
                    Operational Control Center
                </p>
            </div>
            <div class="teacher-session-badge">
                Teaching Mode Active
            </div>
        </div>

        <?php
$announcements = dubez_get_announcements_for_user(get_current_user_id());
?>

<?php if (!empty($announcements)) : ?>
    <div class="teacher-section" style="margin-top:20px;">
        <h2>Announcements</h2>
        <ul style="list-style:none; padding-left:0;">
            <?php foreach ($announcements as $notice) : ?>
                <li style="margin-bottom:10px; padding:10px; background:#f3f4f6; border-radius:6px;">
                    <strong><?php echo esc_html($notice->subject); ?></strong><br>
                    <small><?php echo esc_html($notice->created_at); ?></small>
                    <p><?php echo esc_html($notice->message_body); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

        <?php echo dubez_last_updated_timestamp(); ?>

        <!-- TAB NAVIGATION -->
        <div class="teacher-tabs">
            <a href="<?php echo esc_url(add_query_arg('tab','overview')); ?>">Overview</a>
            <a href="<?php echo esc_url(add_query_arg('tab','assignments')); ?>">Assignments</a>
            <a href="<?php echo esc_url(add_query_arg('tab','grading')); ?>">Grading</a>
            <a href="<?php echo esc_url(add_query_arg('tab','attendance')); ?>">Attendance</a>
        </div>

        <!-- OVERVIEW TAB -->
        <?php if ($active_tab=='overview'): ?>
            <div class="teacher-section">
                <?php echo do_shortcode('[dubez_teacher_dashboard]'); ?>
            </div>
        <?php endif; ?>

        <!-- ASSIGNMENT TAB -->
        <?php if ($active_tab=='assignments'): ?>
            <div class="teacher-section">
                <?php echo do_shortcode('[dubez_teacher_upload]'); ?>
            </div>
        <?php endif; ?>

        <!-- GRADING TAB -->
        <?php if ($active_tab=='grading'): ?>
            <div class="teacher-section">
                <?php echo do_shortcode('[dubez_teacher_view_submissions]'); ?>
            </div>
        <?php endif; ?>

        <!-- ATTENDANCE TAB -->
        <?php if ($active_tab=='attendance'): ?>
            <div class="teacher-section">
                <?php echo do_shortcode('[dubez_teacher_attendance]'); ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>