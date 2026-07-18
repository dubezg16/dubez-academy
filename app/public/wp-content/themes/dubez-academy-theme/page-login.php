<?php
/**
 * Template Name: Elite Login
 */

get_header();

$role = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : '';

$role_labels = [
    'school_admin' => 'Administration Portal',
    'teacher'      => 'Teacher Portal',
    'student'      => 'Student Portal',
    'parent'       => 'Parent Portal',
];

$current_portal = isset($role_labels[$role]) ? $role_labels[$role] : 'Secure Portal Login';

$redirect_url = home_url('/');

if ($role === 'school_admin') {
    $redirect_url = admin_url('admin.php?page=dubez-academy-overview');
} elseif ($role === 'teacher') {
    $redirect_url = home_url('/teacher-dashboard/');
} elseif ($role === 'student') {
    $redirect_url = home_url('/student-portal/');
} elseif ($role === 'parent') {
    $redirect_url = home_url('/parent-portal/');
}
?>

<section class="elite-login">
    <div class="login-left">
        <div class="login-branding">
            <h1>Dubez Academy</h1>
            <p>Institutional Academic Access</p>
        </div>
    </div>

    <div class="login-right">
        <div class="login-panel">

            <h2><?php echo esc_html($current_portal); ?></h2>

            <?php
            wp_login_form(array(
                'redirect'       => $redirect_url,
                'form_id'        => 'elite-login-form',
                'label_username' => 'Username',
                'label_password' => 'Password',
                'label_log_in'   => 'Login',
                'remember'       => true
            ));
            ?>

        </div>
    </div>
</section>

<?php get_footer(); ?>