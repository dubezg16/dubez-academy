<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Run Automated Alerts
 */
function dubez_run_alert_engine() {

    global $wpdb;

    $context = dubez_get_academic_context();

    // ✅ Fee Alerts
    $billing = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT student_id, id
             FROM {$wpdb->prefix}dubez_student_billing
             WHERE status != 'paid'
             AND academic_year = %s
             AND term = %s",
            $context['year'],
            $context['term']
        )
    );

    foreach ($billing as $record) {

        $wpdb->insert(
            $wpdb->prefix . 'dubez_messages',
            array(
                'sender_id'    => 0,
                'recipient_id' => $record->student_id,
                'role_target'  => null,
                'subject'      => 'Outstanding Fee Alert',
                'message_body' => 'You have outstanding school fees for the current term.',
                'message_type' => 'fee_alert',
                'status'       => 'unread',
                'created_at'   => current_time('mysql')
            ),
            array('%d','%d','%s','%s','%s','%s','%s','%s')
        );
    }
}

/**
 * Trigger Alert Engine On Init (Optional Cron Later)
 */
if (!wp_next_scheduled('dubez_alert_cron')) {
    wp_schedule_event(time(), 'hourly', 'dubez_alert_cron');
}

if (!wp_next_scheduled('dubez_alert_cron')) {
    wp_schedule_event(time(), 'hourly', 'dubez_alert_cron');
}

add_action('dubez_alert_cron', 'dubez_run_alert_engine');