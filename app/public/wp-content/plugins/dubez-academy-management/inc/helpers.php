<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Student Class ID
 */
function dubez_get_student_class_id($student_id) {

    global $wpdb;

    // First try relational ID
    $class_id = get_user_meta($student_id, 'student_class_id', true);

    if (!empty($class_id)) {
        return intval($class_id);
    }

    // Fallback to string mapping (legacy support)
    $class_name = get_user_meta($student_id, 'student_class', true);

    if (empty($class_name)) {
        return null;
    }

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dubez_classes WHERE class_name = %s LIMIT 1",
            $class_name
        )
    );
}

/**
 * Last Updated Timestamp
 */
function dubez_last_updated_timestamp() {
    return '<p style="font-size:12px;color:#6b7280;margin-top:10px;">
        Last Updated: ' . esc_html(current_time('mysql')) . '
    </p>';
}

/**
 * Get Teacher Assigned Class
 */
function dubez_get_teacher_class_id($teacher_id) {
    return get_user_meta($teacher_id, 'teacher_class_id', true);
}

function dubez_is_term_locked() {
    return get_option('dubez_term_locked', false);
}

function dubez_log_action($action_type, $description) {

    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'dubez_audit_log',
        [
            'user_id'     => get_current_user_id(),
            'action_type' => $action_type,
            'description' => $description,
            'term'        => get_option('dubez_current_term'),
            'academic_year'=> get_option('dubez_current_academic_year'),
            'created_at'  => current_time('mysql')
        ],
        ['%d','%s','%s','%s','%s','%s']
    );
}