<?php
/**
 * Main Header
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<header class="main-header">
    <div class="nav-container">

        <div class="logo">
            <a href="<?php echo home_url('/'); ?>">DUBEZ ACADEMY</a>
        </div>

        <?php if (!is_user_logged_in()) : ?>

            <nav class="nav-links" id="navMenu">
                <ul>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Academics</a></li>
                    <li><a href="#">Admissions</a></li>
                    <li>
                        <a href="<?php echo home_url('/portal-access/'); ?>" class="portal-nav-btn">
                            Portal
                        </a>
                    </li>
                </ul>
            </nav>

        <?php else : 
            $current_user = wp_get_current_user();
            $roles = (array) $current_user->roles;
            $portal_url = home_url('/');
            $portal_label = 'Portal';

            if (in_array('administrator', $roles)) {
                $portal_url = admin_url('admin.php?page=dubez-academy-overview');
                $portal_label = 'Admin Dashboard';
            } elseif (in_array('teacher', $roles)) {
                $portal_url = home_url('/teacher-dashboard/');
                $portal_label = 'Teacher Dashboard';
            } elseif (in_array('student', $roles)) {
                $portal_url = home_url('/student-portal/');
                $portal_label = 'Student Portal';
            } elseif (in_array('parent', $roles)) {
                $portal_url = home_url('/parent-portal/');
                $portal_label = 'Parent Portal';
            }
        ?>

            <nav class="portal-nav" id="navMenu">
                <ul>
                    <li><a href="<?php echo home_url('/'); ?>">Home</a></li>
                    <li><a href="<?php echo esc_url($portal_url); ?>"><?php echo esc_html($portal_label); ?></a></li>
                    <li><a href="<?php echo wp_logout_url(home_url('/portal-access/')); ?>" class="portal-nav-btn">Logout</a></li>
                </ul>
            </nav>

        <?php endif; ?>

        <!-- Toggle always last -->
        <button class="nav-toggle" id="navToggle">☰</button>

    </div>
</header>

<?php
if (function_exists('dubez_get_academic_context')) {

    $context = dubez_get_academic_context();

    echo '<div class="dubez-academic-context" style="
        background:#0f172a;
        color:#ffffff;
        padding:10px 20px;
        font-size:14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
    ">';

    echo '<div><strong>Academic Year:</strong> ' . esc_html($context['year']) . '</div>';
    echo '<div><strong>Current Term:</strong> ' . esc_html($context['term']) . '</div>';

    echo '</div>';
}
?>