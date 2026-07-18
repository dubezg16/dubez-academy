<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate Billing For Class
 */
function dubez_generate_billing_for_class($class_id) {

    if (!current_user_can('dubez_generate_billing')) {
        return false;
    }

    global $wpdb;

    $context = dubez_get_academic_context();

    // Get students in class
    $students = get_users(array(
        'meta_key'   => 'student_class_id',
        'meta_value' => intval($class_id)
    ));

    foreach ($students as $student) {

        $wpdb->insert(
            $wpdb->prefix . 'dubez_student_billing',
            array(
                'student_id'    => $student->ID,
                'class_id'      => intval($class_id),
                'academic_year' => $context['year'],
                'term'          => $context['term'],
                'amount_paid'   => 0,
                'status'        => 'unpaid',
                'created_at'    => current_time('mysql')
            ),
            array('%d','%d','%s','%s','%f','%s','%s')
        );
    }

    return true;
}

/**
 * Record Payment
 */
function dubez_record_payment($billing_id, $amount) {

    if (!current_user_can('dubez_record_payment')) {
        return false;
    }

    global $wpdb;

    $billing_id = intval($billing_id);
    $amount     = floatval($amount);

    // Update billing record
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}dubez_student_billing
             SET amount_paid = amount_paid + %f,
                 status = IF(amount_paid + %f >= original_amount, 'paid', 'partial')
             WHERE id = %d",
            $amount,
            $amount,
            $billing_id
        )
    );

    // Insert payment record
    $wpdb->insert(
        $wpdb->prefix . 'dubez_payment_records',
        array(
            'billing_id' => $billing_id,
            'amount_paid'=> $amount,
            'recorded_by'=> get_current_user_id(),
            'created_at' => current_time('mysql')
        ),
        array('%d','%f','%d','%s')
    );

    return true;
}