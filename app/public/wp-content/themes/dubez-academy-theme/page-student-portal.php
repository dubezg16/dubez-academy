<?php
/**
 * Template Name: Student Portal
 */

get_header();

if (!current_user_can('dubez_view_academic_reports')) {
    wp_redirect(home_url('/portal-access/'));
    exit;
}
?>

<section class="student-portal">
    <div class="container">

        <?php
        $user = wp_get_current_user();
        global $wpdb;
        $user_id = get_current_user_id();

        $submissions_table = $wpdb->prefix . "dubez_submissions";
        $attendance_table  = $wpdb->prefix . "dubez_attendance";

        // ✅ Fetch student submissions
        $submissions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT grade FROM $submissions_table WHERE student_id = %d",
                $user_id
            )
        );

        $total = count($submissions);
        $graded = 0;
        $total_score = 0;

        foreach ($submissions as $row) {

            if (!empty($row->numeric_score)) {

                $grade_value = 0;

               
                if ($grade_value > 0) {
                    $graded++;
                    $total_score += $grade_value;
                }
            }
        }

        $average = $graded > 0 ? round($total_score / $graded, 1) : 0;
        $completion_rate = $total > 0 ? round(($graded / $total) * 100, 1) : 0;
        $pending = $total - $graded;

        // ✅ Overall Ranking (Elite Engine)
        $student_class_id = dubez_get_student_class_id($user_id);

        $ranking_data = dubez_get_class_overall_ranking($student_class_id);

        $position = null;
        $total_ranked = count($ranking_data);

        foreach ($ranking_data as $rank_row) {
            if ($rank_row->student_id == $user_id) {
                $position = $rank_row->position;
                break;
            }
        }

        // ✅ Growth (Term Comparison)
        $growth = dubez_get_student_growth($user_id);

        // ✅ Subject-Level Positions (Elite Engine)
            $subject_positions = [];

            $context = dubez_get_academic_context();
            $current_year = $context['year'];

            $subjects = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, subject_name 
                    FROM {$wpdb->prefix}dubez_subjects 
                    WHERE class_id = %d 
                    AND academic_year = %s",
                    $student_class_id,
                    $current_year
                )
            );

            foreach ($subjects as $subject) {

                $subject_ranking = dubez_get_subject_ranking($student_class_id, $subject->id);

                foreach ($subject_ranking as $row) {
                    if ($row->student_id == $user_id) {
                        $subject_positions[] = [
                            'subject' => $subject->subject_name,
                            'position' => $row->position,
                            'total' => count($subject_ranking)
                        ];
                        break;
                    }
                }
            }

        // ✅ Attendance
        $attendance_records = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $attendance_table WHERE student_id = %d",
                $user_id
            )
        );

        $present_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $attendance_table WHERE student_id = %d AND status = 'present'",
                $user_id
            )
        );

        $attendance_rate = $attendance_records > 0 
            ? round(($present_count / $attendance_records) * 100, 1)
            : 0;

        // ✅ Performance Status
        $status_class = "warning";
        $performance_status = "Needs Improvement";

        if ($average >= 85) {
            $performance_status = "Excellent Standing";
            $status_class = "excellent";
        } elseif ($average >= 70) {
            $performance_status = "Good Standing";
            $status_class = "good";
        } elseif ($average >= 60) {
            $performance_status = "Satisfactory";
            $status_class = "satisfactory";
        }
        ?>

        <div class="student-header-block">
            <button class="mode-toggle"></button>
            <div>
                <h1>Student Performance Dashboard</h1>
                <p class="student-subtitle">
                    Academic Overview for <?php echo esc_html($user->display_name); ?>
                </p>
            </div>
            <div class="student-session-badge">
                Performance Monitoring Active
            </div>
        </div>

        <?php
$announcements = dubez_get_announcements_for_user($user_id);
?>

<?php if (!empty($announcements)) : ?>
    <div class="student-section" style="margin-top:20px;">
        <h2>Announcements</h2>
        <ul style="list-style:none; padding-left:0;">
            <?php foreach ($announcements as $notice) : ?>
                <li style="margin-bottom:10px; padding:10px; background:#f3f4f6; border-radius:6px;">
                    <strong><?php echo esc_html($notice->subject); ?></strong><br>
                    <small><?php echo esc_html($notice->created_at); ?></small>
                    <p><?php echo esc_html($notice->message_body); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

        <?php echo dubez_last_updated_timestamp(); ?>

        <p class="student-performance-status <?php echo esc_attr($status_class); ?>">
            <?php echo esc_html($performance_status); ?>
        </p>

        <div class="student-metrics">

            <div class="student-metric-card">
                <h3>Overall Average</h3>
                <span class="metric-value"><?php echo esc_html($average); ?>%</span>
            </div>

            <div class="student-metric-card">
                <h3>Class Position</h3>
                <span class="metric-value">
                    <?php 
                    if ($position !== null) {
                        echo esc_html($position . ' of ' . $total_ranked . ' Ranked');
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </span>
            </div>

            <div class="student-metric-card">
                <h3>Previous Term Average</h3>
                <span class="metric-value">
                    <?php 
                    if (!empty($growth['previous_average'])) {
                        echo esc_html($growth['previous_average']) . '%';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </span>
            </div>

           <div class="student-metric-card">
    <h3>Growth</h3>
    <span class="metric-value">
        <?php 

        if ($growth['status'] === 'No Previous Term' || $growth['status'] === 'No Previous Data') {

            echo '<span style="color:#6b7280;">No Previous Data</span>';

        } else {

            $delta = $growth['delta'];

            if ($delta > 0) {
                echo '<span style="color:#16a34a; font-weight:600;">↑ +' . esc_html($delta) . '%</span>';
            } elseif ($delta < 0) {
                echo '<span style="color:#dc2626; font-weight:600;">↓ ' . esc_html($delta) . '%</span>';
            } else {
                echo '<span style="color:#f59e0b; font-weight:600;">→ 0%</span>';
            }
        }

        ?>
    </span>
</div>

            <div class="student-metric-card">
                <h3>Completion Rate</h3>
                <span class="metric-value"><?php echo esc_html($completion_rate); ?>%</span>
            </div>

            <div class="student-metric-card">
                <h3>Attendance Rate</h3>
                <span class="metric-value"><?php echo esc_html($attendance_rate); ?>%</span>
            </div>

            <div class="student-metric-card">
                <h3>Pending Reviews</h3>
                <span class="metric-value"><?php echo esc_html($pending); ?></span>
            </div>

        </div>

        <?php if (!empty($subject_positions)) : ?>
    <div class="student-subject-positions" style="margin-top:40px;">
        <h3>Subject Positions (<?php echo esc_html($context['term']); ?>)</h3>

        <ul style="list-style:none; padding-left:0;">
            <?php foreach ($subject_positions as $sp) : ?>
                <li style="margin-bottom:8px;">
                    <strong><?php echo esc_html($sp['subject']); ?></strong> — 
                    <?php echo esc_html($sp['position'] . ' of ' . $sp['total']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

        

        <div style="margin-top:40px; background:#f5f5f5; padding:20px;">
        <div class="performance-bar-wrapper">
            <div class="performance-bar">
                <div class="performance-fill" style="width: <?php echo esc_attr($average); ?>%;"></div>
            </div>
            <div class="performance-label">
                Academic Performance Level
            </div>
        </div>

        <div class="student-section">
            <h2>Submit Assignment</h2>
            <?php echo do_shortcode('[dubez_student_submit]'); ?>
        </div>

        <div class="student-section">
            <h2>Academic Record</h2>
            <?php echo do_shortcode('[dubez_student_view_grades]'); ?>
        </div>

    </div>
</section>

<?php get_footer(); ?>