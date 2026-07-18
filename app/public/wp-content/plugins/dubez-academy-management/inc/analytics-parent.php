<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Student Term Averages (All Terms in Academic Year)
 */
function dubez_get_student_term_averages($student_id, $academic_year) {
    global $wpdb;

    if (!$student_id || !$academic_year) {
        return [];
    }

    $table = $wpdb->prefix . 'dubez_submissions';

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT submission_term,
                    AVG(numeric_score) as term_average
             FROM $table
             WHERE student_id = %d
             AND academic_year = %s
             GROUP BY submission_term
             ORDER BY FIELD(submission_term, '1st Term','2nd Term','3rd Term')",
            $student_id,
            $academic_year
        )
    );

    $data = [];

    if ($results) {
        foreach ($results as $row) {
            $data[$row->submission_term] = round((float)$row->term_average, 2);
        }
    }

    return $data;
}


/**
 * Get Student Subject Averages (For Specific Term)
 */
function dubez_get_student_subject_averages($student_id, $term, $academic_year) {
    global $wpdb;

    if (!$student_id || !$term || !$academic_year) {
        return [];
    }

    $submissions = $wpdb->prefix . 'dubez_submissions';
    $postmeta    = $wpdb->prefix . 'postmeta';
    $subjects    = $wpdb->prefix . 'dubez_subjects';

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT subj.id as subject_id,
                    subj.subject_name,
                    AVG(s.numeric_score) as subject_average
             FROM $submissions s
             INNER JOIN $postmeta pm
                ON pm.post_id = s.assignment_id
                AND pm.meta_key = 'assignment_subject_id'
             INNER JOIN $subjects subj
                ON subj.id = pm.meta_value
             WHERE s.student_id = %d
             AND s.submission_term = %s
             AND s.academic_year = %s
             GROUP BY subj.id
             ORDER BY subj.subject_name ASC",
            $student_id,
            $term,
            $academic_year
        )
    );

    $data = [];

    if ($results) {
        foreach ($results as $row) {
            $data[] = [
                'subject_id'   => (int) $row->subject_id,
                'subject_name' => $row->subject_name,
                'average'      => round((float)$row->subject_average, 2),
            ];
        }
    }

    return $data;
}


/**
 * Get Attendance Rate by Term
 */
function dubez_get_student_attendance_by_term($student_id, $academic_year) {
    global $wpdb;

    if (!$student_id || !$academic_year) {
        return [];
    }

    $table = $wpdb->prefix . 'dubez_attendance';

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT term,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as presents,
                    COUNT(*) as total
             FROM $table
             WHERE student_id = %d
             AND academic_year = %s
             GROUP BY term
             ORDER BY FIELD(term, '1st Term','2nd Term','3rd Term')",
            $student_id,
            $academic_year
        )
    );

    $data = [];

    if ($results) {
        foreach ($results as $row) {
            $rate = 0;

            if ((int)$row->total > 0) {
                $rate = ((int)$row->presents / (int)$row->total) * 100;
            }

            $data[$row->term] = round($rate, 1);
        }
    }

    return $data;
}


/**
 * Determine Student Risk Level
 */
function dubez_get_student_risk_level($student_id, $term, $academic_year) {

    if (!function_exists('dubez_get_student_average') ||
        !function_exists('dubez_get_student_attendance_rate')) {
        return 'stable';
    }

    $average    = dubez_get_student_average($student_id, $term, $academic_year);
    $attendance = dubez_get_student_attendance_rate($student_id, $term, $academic_year);

    if ($average < 40 || $attendance < 50) {
        return 'critical';
    }

    if ($average < 50 || $attendance < 60) {
        return 'warning';
    }

    return 'stable';
}