<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Overall Ranking
 */
function dubez_get_class_overall_ranking($class_id) {

    global $wpdb;

    $context = dubez_get_academic_context();

    return $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT 
                s.student_id,
                AVG(s.numeric_score) AS overall_average,
                RANK() OVER (ORDER BY AVG(s.numeric_score) DESC) AS position
            FROM {$wpdb->prefix}dubez_submissions s
            INNER JOIN {$wpdb->prefix}usermeta um 
                ON s.student_id = um.user_id
            WHERE um.meta_key = 'student_class_id'
            AND um.meta_value = %d
            AND s.academic_year = %s
            AND s.submission_term = %s
            GROUP BY s.student_id
            ",
            $class_id,
            $context['year'],
            $context['term']
        )
    );
}

/**
 * Subject Ranking
 */
function dubez_get_subject_ranking($class_id, $subject_id) {

    global $wpdb;

    $context = dubez_get_academic_context();

    return $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT 
                s.student_id,
                AVG(s.numeric_score) AS overall_average,
                RANK() OVER (ORDER BY AVG(s.numeric_score) DESC) AS position
            FROM {$wpdb->prefix}dubez_submissions s
            INNER JOIN {$wpdb->prefix}postmeta pm 
                ON s.assignment_id = pm.post_id
            INNER JOIN {$wpdb->prefix}usermeta um 
                ON s.student_id = um.user_id
            WHERE pm.meta_key = 'assignment_subject_id'
            AND pm.meta_value = %d
            AND um.meta_key = 'student_class_id'
            AND um.meta_value = %d
            AND s.academic_year = %s
            AND s.submission_term = %s
            GROUP BY s.student_id
            ",
            $subject_id,
            $class_id,
            $context['year'],
            $context['term']
        )
    );
}

/**
 * Student Growth (Term Comparison)
 */
function dubez_get_student_growth($student_id) {

    global $wpdb;

    $context = dubez_get_academic_context();

    $current_average = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(numeric_score)
             FROM {$wpdb->prefix}dubez_submissions
             WHERE student_id = %d
             AND academic_year = %s
             AND submission_term = %s",
            $student_id,
            $context['year'],
            $context['term']
        )
    );

    $previous_term = null;

    if ($context['term'] === '2nd Term') {
        $previous_term = '1st Term';
    } elseif ($context['term'] === '3rd Term') {
        $previous_term = '2nd Term';
    }

    if (!$previous_term) {
        return array(
            'status' => 'No Previous Term',
            'delta' => 0,
            'previous_average' => null
        );
    }

    $previous_average = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(numeric_score)
             FROM {$wpdb->prefix}dubez_submissions
             WHERE student_id = %d
             AND academic_year = %s
             AND submission_term = %s",
            $student_id,
            $context['year'],
            $previous_term
        )
    );

    if (!$previous_average) {
        return array(
            'status' => 'No Previous Data',
            'delta' => 0,
            'previous_average' => null
        );
    }

    $delta = round($current_average - $previous_average, 1);

    return array(
        'status' => 'OK',
        'delta' => $delta,
        'previous_average' => round($previous_average, 1)
    );
}