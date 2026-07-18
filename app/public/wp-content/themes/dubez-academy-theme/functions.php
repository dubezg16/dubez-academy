<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ==========================================
   THEME SETUP
========================================== */

function dubez_academy_setup() {

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'dubez-academy' ),
    ) );
}
add_action( 'after_setup_theme', 'dubez_academy_setup' );


/* ==========================================
   ENQUEUE STYLES
========================================== */

function dubez_academy_scripts() {

    wp_enqueue_style(
        'dubez-fonts',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap'
    );

    wp_enqueue_style(
        'dubez-style',
        get_stylesheet_uri(),
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'dubez-mode-toggle',
        get_template_directory_uri() . '/js/mode-toggle.js',
        array(),
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'dubez_academy_scripts' );


/* ==========================================
   PORTAL ACCESS GUARD (SAFE VERSION)
========================================== */

function dubez_portal_access_guard() {

    if ( is_admin() ) {
        return; // NEVER interfere with admin area
    }

    if ( ! is_page() ) {
        return;
    }

    if ( is_page('portal-login') ) {
        return;
    }

    if ( ! is_user_logged_in() ) {

        if (
            is_page('admin-dashboard') ||
            is_page('teacher-dashboard') ||
            is_page('student-portal') ||
            is_page('parent-portal')
        ) {
            wp_redirect( home_url('/portal-login/') );
            exit;
        }

        return;
    }

    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    if ( is_page('teacher-portal') && ! in_array('teacher', $roles) ) {
        wp_redirect( home_url('/portal-login/') );
        exit;
    }

    if ( is_page('student-portal') && ! in_array('student', $roles) ) {
        wp_redirect( home_url('/portal-login/') );
        exit;
    }

    if ( is_page('parent-portal') && ! in_array('parent', $roles) ) {
        wp_redirect( home_url('/portal-login/') );
        exit;
    }
}
add_action( 'template_redirect', 'dubez_portal_access_guard' );


/* ==========================================
   SAFE WP-ADMIN LOCK
========================================== */

function dubez_lock_wp_admin() {

    if ( ! is_admin() || defined('DOING_AJAX') ) {
        return;
    }

    if ( ! is_user_logged_in() ) {
        return;
    }

    $user = wp_get_current_user();

    // Always allow true administrators
    if ( in_array('administrator', (array) $user->roles) ) {
        return;
    }

    if ( in_array('teacher', (array) $user->roles) ) {
       wp_redirect(home_url('/teacher-portal/'));
        exit;
    }

    if ( in_array('student', (array) $user->roles) ) {
        wp_redirect( home_url('/student-portal/') );
        exit;
    }

    if ( in_array('parent', (array) $user->roles) ) {
        wp_redirect( home_url('/parent-portal/') );
        exit;
    }
}
add_action( 'admin_init', 'dubez_lock_wp_admin' );


/* ==========================================
   HIDE ADMIN BAR FOR NON-ADMIN USERS
========================================== */

function dubez_hide_admin_bar_for_non_admins() {

    if ( ! current_user_can('administrator') ) {
        show_admin_bar(false);
    }
}
add_action( 'after_setup_theme', 'dubez_hide_admin_bar_for_non_admins' );


/* ==========================================
   LOAD DASHICONS FOR LOGGED IN USERS
========================================== */

function dubez_load_dashicons() {
    if ( is_user_logged_in() ) {
        wp_enqueue_style('dashicons');
    }
}
add_action( 'wp_enqueue_scripts', 'dubez_load_dashicons' );


/* ==========================================
   LOGIN REDIRECT BY ROLE
========================================== */

function dubez_login_redirect($redirect_to, $request, $user) {

    if (isset($user->roles) && is_array($user->roles)) {

        if (in_array('teacher', $user->roles)) {
            return home_url('/teacher-portal/');
        }

        if (in_array('student', $user->roles)) {
            return home_url('/student-portal/');
        }

        if (in_array('parent', $user->roles)) {
            return home_url('/parent-portal/');
        }
    }

    return $redirect_to;
}
add_filter('login_redirect', 'dubez_login_redirect', 10, 3);