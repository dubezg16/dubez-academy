<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Institutional Roles
 */
function dubez_register_roles() {

    // School Admin
    add_role('school_admin', 'School Admin', array(
        'read' => true,

        'dubez_manage_context'       => true,
        'dubez_manage_fees'          => true,
        'dubez_generate_billing'     => true,
        'dubez_record_payment'       => true,
        'dubez_approve_payment'      => true,
        'dubez_send_announcement'    => true,
        'dubez_mark_attendance'      => true,
        'dubez_grade_assignment'     => true,
        'dubez_view_academic_reports'=> true,
    ));

    // Teacher
    add_role('teacher', 'Teacher', array(
        'read' => true,

        'dubez_mark_attendance'      => true,
        'dubez_grade_assignment'     => true,
        'dubez_send_announcement'    => true,
        'dubez_view_academic_reports'=> true,
    ));

    // Student
    add_role('student', 'Student', array(
        'read' => true,
        'dubez_view_academic_reports'=> true,
    ));

    // Parent
    add_role('parent', 'Parent', array(
        'read' => true,
        'dubez_view_academic_reports'=> true,
    ));
}

/**
 * Assign Capabilities to WordPress Administrator
 */
function dubez_extend_administrator_caps() {

    $admin = get_role('administrator');

    if ($admin) {
        $admin->add_cap('dubez_manage_context');
        $admin->add_cap('dubez_manage_fees');
        $admin->add_cap('dubez_generate_billing');
        $admin->add_cap('dubez_record_payment');
        $admin->add_cap('dubez_approve_payment');
        $admin->add_cap('dubez_send_announcement');
        $admin->add_cap('dubez_mark_attendance');
        $admin->add_cap('dubez_grade_assignment');
        $admin->add_cap('dubez_view_academic_reports');
    }
}

add_action('init', 'dubez_register_roles');
add_action('init', 'dubez_extend_administrator_caps');