<?php
/**
 * Template Name: Portal Access
 */

get_header();
?>

<section class="portal-hero">
    <div class="portal-hero-inner">
        <h1>Dubez Academy Portal Access</h1>
        <p>Structured access for authorized members of our academic institution.</p>
    </div>
</section>

<section class="portal-grid-section">
    <div class="portal-grid">

        <div class="portal-card admin">
            <h3>Administration Portal</h3>
            <p>Institutional control, reporting oversight, and academic governance.</p>
           <a href="<?php echo wp_login_url(admin_url('admin.php?page=dubez-academy-overview')); ?>" class="portal-btn">
                Enter Admin Portal
            </a>
        </div>

        <div class="portal-card teacher">
            <h3>Teacher Portal</h3>
            <p>Class management, grading system, attendance monitoring.</p>
            <a href="<?php echo wp_login_url(home_url('/teacher-dashboard/')); ?>" class="portal-btn">
                Enter Teacher Portal
            </a>
        </div>

        <div class="portal-card student">
            <h3>Student Portal</h3>
            <p>Academic performance, transcripts, assignments, attendance.</p>
           <a href="<?php echo wp_login_url(home_url('/student-portal/')); ?>" class="portal-btn">
                Enter Student Portal
            </a>
        </div>

        <div class="portal-card parent">
            <h3>Parent Portal</h3>
            <p>Monitor performance, attendance trends, and academic reports.</p>
           <a href="<?php echo wp_login_url(home_url('/parent-portal/')); ?>" class="portal-btn">
                Enter Parent Portal
            </a>
        </div>

    </div>
</section>

<?php get_footer(); ?>