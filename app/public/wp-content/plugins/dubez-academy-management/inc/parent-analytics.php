<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Student Term Averages
 */
function dubez_get_student_term_averages($student_id, $academic_year) {

    global $wpdb;

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT submission_term, AVG(numeric_score) AS term_average
             FROM {$wpdb->prefix}dubez_submissions
             WHERE student_id = %d
             AND academic_year = %s
             GROUP BY submission_term",
            $student_id,
            $academic_year
        )
    );

    $data = [];

    foreach ($results as $row) {
        $data[$row->submission_term] = round($row->term_average, 2);
    }

    return $data;
}

/**
 * Subject Averages
 */
function dubez_get_student_subject_averages($student_id, $term, $academic_year) {

    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT subj.subject_name,
                   AVG(s.numeric_score) AS average
            FROM {$wpdb->prefix}dubez_submissions s
            INNER JOIN {$wpdb->prefix}postmeta pm
                ON s.assignment_id = pm.post_id
            INNER JOIN {$wpdb->prefix}dubez_subjects subj
                ON pm.meta_value = subj.id
            WHERE s.student_id = %d
            AND s.submission_term = %s
            AND s.academic_year = %s
            AND pm.meta_key = 'assignment_subject_id'
            GROUP BY subj.id
            ",
            $student_id,
            $term,
            $academic_year
        )
    );
}
/**
 * Attendance By Term
 */
function dubez_get_student_attendance_by_term($student_id, $academic_year) {

    global $wpdb;

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT term,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS presents,
                COUNT(*) AS total
             FROM {$wpdb->prefix}dubez_attendance
             WHERE student_id = %d
             AND academic_year = %s
             GROUP BY term",
            $student_id,
            $academic_year
        )
    );

    $data = [];

    foreach ($results as $row) {
        $rate = $row->total > 0
            ? round(($row->presents / $row->total) * 100, 1)
            : 0;

        $data[$row->term] = $rate;
    }

    return $data;
}

/**
 * Risk Level
 */
function dubez_get_student_risk_level($student_id, $term, $academic_year) {

    global $wpdb;

    $average = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(numeric_score)
             FROM {$wpdb->prefix}dubez_submissions
             WHERE student_id = %d
             AND submission_term = %s
             AND academic_year = %s",
            $student_id,
            $term,
            $academic_year
        )
    );

    if ($average < 40) {
        return 'critical';
    }

    if ($average < 50) {
        return 'warning';
    }

    return 'stable';
}