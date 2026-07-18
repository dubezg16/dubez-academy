<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Student Submission Shortcode
 */
function dubez_student_submit_shortcode() {

    if (!current_user_can('student')) {
        return '<p>Access restricted to students only.</p>';
    }

    if (dubez_is_term_locked()) {
    return '<div class="notice notice-error">
        Term is locked. Submission disabled.
    </div>';
}

    global $wpdb;

    $table_name = $wpdb->prefix . 'dubez_submissions';
    $student_id = get_current_user_id();
    $message = '';

    // Handle Submission
    if (isset($_POST['dubez_student_submit_btn'])) {

        if (
            !isset($_POST['dubez_student_nonce']) ||
            !wp_verify_nonce($_POST['dubez_student_nonce'], 'dubez_student_action')
        ) {
            $message = '<div class="notice notice-error">Security verification failed.</div>';
        } else {

            $assignment_id   = intval($_POST['assignment_id']);
            $submission_text = wp_kses_post($_POST['submission_text']);
            $file_url        = '';

            // Secure Upload
            if (
                isset($_FILES['submission_file']) &&
                is_array($_FILES['submission_file']) &&
                $_FILES['submission_file']['error'] === UPLOAD_ERR_OK
            ) {

                require_once(ABSPATH . 'wp-admin/includes/file.php');

                $allowed_mimes = array(
                    'pdf'  => 'application/pdf',
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                );

                $file = $_FILES['submission_file'];

                $max_file_size = 5 * 1024 * 1024;

                if ($file['size'] > $max_file_size) {
                    wp_die('File exceeds maximum size of 5MB.');
                }

                $filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);

                if (!$filetype['ext'] || !isset($allowed_mimes[$filetype['ext']])) {
                    wp_die('Invalid file type.');
                }

                $uploaded_file = wp_handle_upload(
                    $file,
                    array(
                        'test_form' => false,
                        'mimes'     => $allowed_mimes
                    )
                );

                if (isset($uploaded_file['error'])) {
                    wp_die($uploaded_file['error']);
                }

                $file_url = $uploaded_file['url'];
            }

            if (empty($assignment_id)) {
                $message = '<div class="notice notice-error">Please select an assignment.</div>';
            }

            if (empty($submission_text) && empty($file_url)) {
                $message = '<div class="notice notice-error">Provide text or upload a file.</div>';
            }

            if (empty($message)) {

                $context = dubez_get_academic_context();

                $wpdb->insert(
                    $table_name,
                    array(
                        'assignment_id'   => $assignment_id,
                        'student_id'      => $student_id,
                        'submission_text' => $submission_text,
                        'submission_file' => $file_url,
                        'submission_term' => $context['term'],
                        'academic_year'   => $context['year'],
                        'submission_date' => current_time('mysql')
                    ),
                    array('%d','%d','%s','%s','%s','%s','%s')
                );

                $message = '<div class="notice notice-success">Submission successful!</div>';
            }
        }
    }

    ob_start();
    ?>

    <div class="dubez-submission-form">
        <?php echo $message; ?>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('dubez_student_action','dubez_student_nonce'); ?>

            <?php
$student_class_id = dubez_get_student_class_id($student_id);
$context = dubez_get_academic_context();

$assignments = get_posts(array(
    'post_type'      => 'assignments',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => 'assignment_class_id',
            'value'   => (int) $student_class_id,
            'compare' => '='
        ),
        array(
            'key'     => 'assignment_term',
            'value'   => $context['term'],
            'compare' => '='
        )
    )
));
?>

<p>
    <label>Select Assignment</label><br>
    <select name="assignment_id" required>
        <option value="">-- Choose Assignment --</option>
        <?php foreach ($assignments as $assignment): ?>
            <option value="<?php echo esc_attr($assignment->ID); ?>">
                <?php echo esc_html($assignment->post_title); ?>
            </option>
        <?php endforeach; ?>
    </select>
</p>

            <p>
                <label>Your Answer</label><br>
                <textarea name="submission_text" rows="5"></textarea>
            </p>

            <p>
                <label>Upload File (PDF, Image, DOC)</label><br>
                <input type="file" name="submission_file">
            </p>

            <p>
                <input type="submit" name="dubez_student_submit_btn" value="Submit Assignment">
            </p>
        </form>
    </div>

    <?php

    return ob_get_clean();
}

add_shortcode('dubez_student_submit', 'dubez_student_submit_shortcode');

/**
 * Student View Grades Shortcode
 */
function dubez_student_view_grades_shortcode() {

    if (!current_user_can('dubez_view_academic_reports')) {
        return '';
    }

    global $wpdb;

    $student_id = get_current_user_id();
    $context = dubez_get_academic_context();

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.numeric_score, s.grade_letter, s.submission_term,
                    p.post_title AS assignment_title
             FROM {$wpdb->prefix}dubez_submissions s
             INNER JOIN {$wpdb->posts} p ON s.assignment_id = p.ID
             WHERE s.student_id = %d
             AND s.academic_year = %s
             ORDER BY s.submission_date DESC",
            $student_id,
            $context['year']
        )
    );

    if (empty($results)) {
        return '<p>No academic records found.</p>';
    }

    ob_start();
    ?>
    <table class="dubez-grades-table">
        <thead>
            <tr>
                <th>Assignment</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Term</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo esc_html($row->assignment_title); ?></td>
                    <td><?php echo esc_html($row->numeric_score); ?></td>
                    <td><?php echo esc_html($row->grade_letter); ?></td>
                    <td><?php echo esc_html($row->submission_term); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

add_shortcode('dubez_student_view_grades', 'dubez_student_view_grades_shortcode');

/**
 * Parent View Child Grades
 */
function dubez_parent_view_child_grades_shortcode() {

    if (!current_user_can('dubez_view_academic_reports')) {
        return '';
    }

    global $wpdb;

    $parent_id = get_current_user_id();
    $child_id = get_user_meta($parent_id, 'linked_student_id', true);

    if (!$child_id) {
        return '<p>No linked student found.</p>';
    }

    $context = dubez_get_academic_context();

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.numeric_score, s.grade_letter, s.submission_term,
                    p.post_title AS assignment_title
             FROM {$wpdb->prefix}dubez_submissions s
             INNER JOIN {$wpdb->posts} p ON s.assignment_id = p.ID
             WHERE s.student_id = %d
             AND s.academic_year = %s
             ORDER BY s.submission_date DESC",
            $child_id,
            $context['year']
        )
    );

    if (empty($results)) {
        return '<p>No academic records found.</p>';
    }

    ob_start();
    ?>
    <table class="widefat fixed striped" style="margin-top:15px;">
        <thead>
            <tr>
                <th>Assignment</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Term</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo esc_html($row->assignment_title); ?></td>
                    <td><?php echo esc_html($row->numeric_score); ?></td>
                    <td><?php echo esc_html($row->grade_letter); ?></td>
                    <td><?php echo esc_html($row->submission_term); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

add_shortcode('dubez_parent_view_child_grades', 'dubez_parent_view_child_grades_shortcode');