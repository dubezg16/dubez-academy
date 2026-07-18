<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Send Message
 */
function dubez_send_message($data) {

    if (!current_user_can('dubez_send_announcement')) {
        return false;
    }

    global $wpdb;

    return $wpdb->insert(
        $wpdb->prefix . 'dubez_messages',
        array(
            'sender_id'    => get_current_user_id(),
            'recipient_id' => isset($data['recipient_id']) ? intval($data['recipient_id']) : null,
            'role_target'  => isset($data['role_target']) ? sanitize_text_field($data['role_target']) : null,
            'subject'      => sanitize_text_field($data['subject']),
            'message_body' => wp_kses_post($data['message_body']),
            'message_type' => sanitize_text_field($data['message_type']),
            'status'       => 'unread',
            'created_at'   => current_time('mysql')
        ),
        array('%d','%d','%s','%s','%s','%s','%s','%s')
    );
}

/**
 * Get Messages For User
 */
function dubez_get_messages_for_user($user_id) {

    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dubez_messages
             WHERE recipient_id = %d
                OR role_target IN (
                    SELECT meta_value FROM {$wpdb->prefix}usermeta
                    WHERE user_id = %d
                    AND meta_key = '{$wpdb->prefix}capabilities'
                )
             ORDER BY created_at DESC",
            $user_id,
            $user_id
        )
    );
}

/**
 * Get Unread Message Count
 */
function dubez_get_unread_message_count($user_id = null) {

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    global $wpdb;

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->prefix}dubez_messages
             WHERE recipient_id = %d
             AND status = 'unread'",
            $user_id
        )
    );
}

/**
 * Get Announcements For User
 */
function dubez_get_announcements_for_user($user_id) {

    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT subject, message_body, created_at
             FROM {$wpdb->prefix}dubez_messages
             WHERE 
                 (recipient_id = %d OR role_target = 'all')
             AND message_type = 'announcement'
             ORDER BY created_at DESC",
            $user_id
        )
    );
}