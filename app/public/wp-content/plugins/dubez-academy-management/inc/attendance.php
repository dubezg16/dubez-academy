<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mark Attendance
 */
function dubez_mark_attendance($student_id, $status) {

    if (!current_user_can('dubez_mark_attendance')) {
        return false;
    }

    global $wpdb;

    $context = dubez_get_academic_context();

    return $wpdb->insert(
        $wpdb->prefix . 'dubez_attendance',
        array(
            'student_id'   => intval($student_id),
            'attendance_date' => current_time('Y-m-d'),
            'term'         => $context['term'],
            'academic_year'=> $context['year'],
            'status'       => sanitize_text_field($status),
            'marked_by'    => get_current_user_id(),
            'created_at'   => current_time('mysql')
        ),
        array('%d','%s','%s','%s','%s','%d','%s')
    );
}