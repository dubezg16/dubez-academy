<?php
if (!defined('ABSPATH')) {
    exit;
}

/* =====================================================
   TEACHER OVERVIEW
===================================================== */
function dubez_teacher_dashboard_shortcode() {

    if (!current_user_can('dubez_grade_assignment')) {
        return '';
    }

    global $wpdb;

    $teacher_id = get_current_user_id();
    $class_id = dubez_get_teacher_class_id($teacher_id);

    if (!$class_id) {
        return '<p>No class assigned to this teacher.</p>';
    }

    $context = dubez_get_academic_context();

    // Total Assignments
    $total_assignments = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = 'assignments'
             AND post_author = %d",
            $teacher_id
        )
    );

    // Total Submissions
    $total_submissions = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dubez_submissions s
             JOIN {$wpdb->prefix}usermeta um
               ON s.student_id = um.user_id
             WHERE um.meta_key = 'student_class_id'
             AND um.meta_value = %d",
            $class_id
        )
    );

    // Pending Grades
    $pending = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dubez_submissions s
             JOIN {$wpdb->prefix}usermeta um
               ON s.student_id = um.user_id
             WHERE um.meta_key = 'student_class_id'
             AND um.meta_value = %d
             AND s.numeric_score IS NULL",
            $class_id
        )
    );

    // Class Average
    $class_average = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(s.numeric_score)
             FROM {$wpdb->prefix}dubez_submissions s
             JOIN {$wpdb->prefix}usermeta um
               ON s.student_id = um.user_id
             WHERE um.meta_key = 'student_class_id'
             AND um.meta_value = %d
             AND s.academic_year = %s
             AND s.submission_term = %s",
            $class_id,
            $context['year'],
            $context['term']
        )
    );

    $class_average = $class_average ? round($class_average,1) : 0;

    ob_start();
    ?>

 <div class="teacher-dashboard-grid">

    <div class="teacher-card">
        <div class="teacher-card-title">Total Assignments</div>
        <div class="teacher-card-value"><?php echo intval($total_assignments); ?></div>
    </div>

    <div class="teacher-card">
        <div class="teacher-card-title">Total Submissions</div>
        <div class="teacher-card-value"><?php echo intval($total_submissions); ?></div>
    </div>

    <div class="teacher-card">
        <div class="teacher-card-title">Pending Grades</div>
        <div class="teacher-card-value"><?php echo intval($pending); ?></div>
    </div>

    <div class="teacher-card">
        <div class="teacher-card-title">Class Average</div>
        <div class="teacher-card-value"><?php echo esc_html($class_average); ?>%</div>
    </div>

</div>

    <?php

    return ob_get_clean();
}
add_shortcode('dubez_teacher_dashboard', 'dubez_teacher_dashboard_shortcode');


/* =====================================================
   TEACHER ASSIGNMENT UPLOAD
===================================================== */
function dubez_teacher_upload_shortcode() {

    if (!current_user_can('dubez_grade_assignment')) {
        return '';
    }
    if (dubez_is_term_locked()) {
    return '<div class="notice notice-error">
        Term is locked. Assignment creation disabled.
    </div>';
}

if (dubez_is_term_locked()) {
    return '<div class="notice notice-error">
        Term is locked. Grading disabled.
    </div>';
}

    global $wpdb;

    $message = '';
    $teacher_id = get_current_user_id();
    $class_id = dubez_get_teacher_class_id($teacher_id);

    if (!$class_id) {
        return '<p>No class assigned to this teacher.</p>';
    }

    $context = dubez_get_academic_context();

    $subjects = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, subject_name
             FROM {$wpdb->prefix}dubez_subjects
             WHERE class_id = %d
             AND academic_year = %s",
            $class_id,
            $context['year']
        )
    );

    if (isset($_POST['dubez_assignment_submit'])) {

        if (
            !isset($_POST['dubez_assignment_nonce']) ||
            !wp_verify_nonce($_POST['dubez_assignment_nonce'], 'dubez_assignment_action')
        ) {
            $message = '<div class="notice notice-error">Security verification failed.</div>';
        } else {

            $title       = sanitize_text_field($_POST['assignment_title']);
            $description = wp_kses_post($_POST['assignment_description']);
            $subject_id  = intval($_POST['assignment_subject_id']);

            $post_id = wp_insert_post(array(
                'post_title'   => $title,
                'post_content' => $description,
                'post_status'  => 'publish',
                'post_type'    => 'assignments',
                'post_author'  => $teacher_id
            ));

            if ($post_id) {

                update_post_meta($post_id, 'assignment_class_id', $class_id);
                update_post_meta($post_id, 'assignment_subject_id', $subject_id);
                update_post_meta($post_id, 'assignment_term', $context['term']);
                update_post_meta($post_id, 'assignment_academic_year', $context['year']);

                if (
                    isset($_FILES['assignment_file']) &&
                    is_array($_FILES['assignment_file']) &&
                    $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK
                ) {

                    require_once(ABSPATH . 'wp-admin/includes/file.php');

                    $allowed_mimes = array(
                        'pdf'  => 'application/pdf',
                        'jpg'  => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png'  => 'image/png'
                    );

                    $file = $_FILES['assignment_file'];
                    $filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);

                    if ($filetype['ext'] && isset($allowed_mimes[$filetype['ext']])) {

                        $uploaded = wp_handle_upload($file, array(
                            'test_form' => false,
                            'mimes' => $allowed_mimes
                        ));

                        if (!isset($uploaded['error'])) {
                            update_post_meta($post_id, 'assignment_file_url', $uploaded['url']);
                        }
                    }
                }

                $message = '<div class="notice notice-success">Assignment created successfully.</div>';
            }
        }
    }

    ob_start();
    ?>

    <div class="teacher-upload-form">
        <?php echo $message; ?>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('dubez_assignment_action','dubez_assignment_nonce'); ?>

            <p>
                <label>Assignment Title</label><br>
                <input type="text" name="assignment_title" required>
            </p>

            <p>
                <label>Description</label><br>
                <textarea name="assignment_description" rows="5"></textarea>
            </p>

            <p>
                <label>Subject</label><br>
                <select name="assignment_subject_id" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo esc_attr($subject->id); ?>">
                            <?php echo esc_html($subject->subject_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label>Upload Assignment File (PDF/Image)</label><br>
                <input type="file" name="assignment_file">
            </p>

            <p>
                <button type="submit" name="dubez_assignment_submit"
                        class="button button-primary">
                    Create Assignment
                </button>
            </p>
        </form>
    </div>

    <?php

    return ob_get_clean();
}
add_shortcode('dubez_teacher_upload', 'dubez_teacher_upload_shortcode');


/* =====================================================
   TEACHER GRADING
===================================================== */
function dubez_teacher_view_submissions_shortcode() {

    if (!current_user_can('dubez_grade_assignment')) {
        return '';
    }

    global $wpdb;

    $message = '';
    $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';

    if (isset($_POST['dubez_grade_submit'])) {

        if (
            !isset($_POST['dubez_grade_nonce']) ||
            !wp_verify_nonce($_POST['dubez_grade_nonce'], 'dubez_grade_action')
        ) {
            $message = '<div class="notice notice-error">Security verification failed.</div>';
        } else {

            $submission_id = intval($_POST['submission_id']);
            $numeric_score = intval($_POST['numeric_score']);

            if ($numeric_score >= 70) {
                $grade_letter = 'A';
            } elseif ($numeric_score >= 60) {
                $grade_letter = 'B';
            } elseif ($numeric_score >= 50) {
                $grade_letter = 'C';
            } elseif ($numeric_score >= 45) {
                $grade_letter = 'D';
            } else {
                $grade_letter = 'F';
            }

            $wpdb->update(
                $wpdb->prefix . 'dubez_submissions',
                array(
                    'numeric_score' => $numeric_score,
                    'grade_letter'  => $grade_letter
                ),
                array('id' => $submission_id),
                array('%d','%s'),
                array('%d')
            );

            $message = '<div class="notice notice-success">Grade saved successfully.</div>';
        }
    }

    $where = ($filter === 'ungraded') ? "WHERE s.numeric_score IS NULL" : "";

    $submissions = $wpdb->get_results(
        "SELECT s.*, u.display_name, p.post_title
         FROM {$wpdb->prefix}dubez_submissions s
         JOIN {$wpdb->users} u ON s.student_id = u.ID
         JOIN {$wpdb->posts} p ON s.assignment_id = p.ID
         $where
         ORDER BY s.submission_date DESC"
    );

    ob_start();
    ?>

    <div>
        <?php echo $message; ?>

        <p>
            <a href="<?php echo esc_url(add_query_arg('filter','all')); ?>">All</a> |
            <a href="<?php echo esc_url(add_query_arg('filter','ungraded')); ?>">Ungraded</a>
        </p>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Assignment</th>
                    <th>Submission</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->display_name); ?></td>
                        <td><?php echo esc_html($row->post_title); ?></td>
                        <td><?php echo esc_html($row->submission_text); ?></td>
                        <td>
                            <form method="post">
                                <?php wp_nonce_field('dubez_grade_action','dubez_grade_nonce'); ?>
                                <input type="hidden" name="submission_id" value="<?php echo esc_attr($row->id); ?>">
                                <input type="number" name="numeric_score"
                                       value="<?php echo esc_attr($row->numeric_score); ?>"
                                       min="0" max="100" required>
                                <button type="submit" name="dubez_grade_submit"
                                        class="button button-primary">
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php

    return ob_get_clean();
}
add_shortcode('dubez_teacher_view_submissions', 'dubez_teacher_view_submissions_shortcode');


/* =====================================================
   TEACHER ATTENDANCE
===================================================== */
function dubez_teacher_attendance_shortcode() {

    if (!current_user_can('dubez_mark_attendance')) {
        return '';
    }

    global $wpdb;

    $teacher_id = get_current_user_id();
    $class_id = dubez_get_teacher_class_id($teacher_id);

    if (!$class_id) {
        return '<p>No class assigned to this teacher.</p>';
    }

    $context = dubez_get_academic_context();
    $message = '';

    if (isset($_POST['dubez_attendance_submit'])) {

        if (
            !isset($_POST['dubez_attendance_nonce']) ||
            !wp_verify_nonce($_POST['dubez_attendance_nonce'], 'dubez_attendance_action')
        ) {
            $message = '<div class="notice notice-error">Security verification failed.</div>';
        } else {

            foreach ($_POST['attendance'] as $student_id => $status) {

                $wpdb->insert(
                    $wpdb->prefix . 'dubez_attendance',
                    array(
                        'student_id'    => intval($student_id),
                        'attendance_date' => current_time('Y-m-d'),
                        'term'          => $context['term'],
                        'academic_year' => $context['year'],
                        'status'        => sanitize_text_field($status),
                        'marked_by'     => $teacher_id,
                        'created_at'    => current_time('mysql')
                    ),
                    array('%d','%s','%s','%s','%s','%d','%s')
                );
            }

            $message = '<div class="notice notice-success">Attendance recorded successfully.</div>';
        }
    }

    $students = get_users(array(
        'meta_key'   => 'student_class_id',
        'meta_value' => $class_id
    ));

    ob_start();
    ?>

    <div>
        <?php echo $message; ?>

        <form method="post">
            <?php wp_nonce_field('dubez_attendance_action','dubez_attendance_nonce'); ?>

            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Present</th>
                        <th>Absent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo esc_html($student->display_name); ?></td>
                            <td>
                                <input type="radio"
                                       name="attendance[<?php echo esc_attr($student->ID); ?>]"
                                       value="present" required>
                            </td>
                            <td>
                                <input type="radio"
                                       name="attendance[<?php echo esc_attr($student->ID); ?>]"
                                       value="absent" required>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top:15px;">
                <button type="submit" name="dubez_attendance_submit"
                        class="button button-primary">
                    Save Attendance
                </button>
            </p>
        </form>
    </div>

    <?php

    return ob_get_clean();
}
add_shortcode('dubez_teacher_attendance', 'dubez_teacher_attendance_shortcode');