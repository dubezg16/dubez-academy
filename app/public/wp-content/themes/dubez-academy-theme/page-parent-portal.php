<?php
/**
 * Template Name: Parent Portal
 */

get_header();

if (!current_user_can('dubez_view_academic_reports')) {
    wp_redirect(home_url('/portal-access/'));
    exit;
}
?>

<section class="parent-portal">
    <div class="container">

        <?php
        $user = wp_get_current_user();
        global $wpdb;

        // ✅ Assuming parent is linked to child via user meta
        $child_id = get_user_meta($user->ID, 'linked_student_id', true);

        if (!$child_id) {
            echo '<p>No linked student found.</p>';
            get_footer();
            return;
        }

        $submissions_table = $wpdb->prefix . "dubez_submissions";
        $attendance_table  = $wpdb->prefix . "dubez_attendance";

       // ✅ Academic Context
$context = dubez_get_academic_context();
$current_term = $context['term'];
$current_year = $context['year'];

/**
 * Phase 5 — Advanced Parent Intelligence
 * Data Wiring Only (No Rendering)
 */
$term_trend_data       = dubez_get_student_term_averages($child_id, $current_year);
$subject_trend_data    = dubez_get_student_subject_averages($child_id, $current_term, $current_year);
$attendance_trend_data = dubez_get_student_attendance_by_term($child_id, $current_year);
$risk_level            = dubez_get_student_risk_level($child_id, $current_term, $current_year);


wp_enqueue_script('chart-js');
wp_enqueue_script('dubez-parent-charts');

wp_localize_script(
    'dubez-parent-charts',
    'dubezParentAnalytics',
    [
        'termTrend'       => $term_trend_data,
        'subjectTrend'    => $subject_trend_data,
        'attendanceTrend' => $attendance_trend_data,
        'currentTerm'     => $current_term,
        'currentYear'     => $current_year,
    ]
);


// ✅ Term-Aware Average
$average = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT AVG(numeric_score)
         FROM $submissions_table
         WHERE student_id = %d
         AND submission_term = %s
         AND numeric_score IS NOT NULL",
        $child_id,
        $current_term
    )
);

$average = $average ? round($average, 1) : 0;

// ✅ Child Class Position
$student_class_id = dubez_get_student_class_id($child_id);
$ranking_data = dubez_get_class_overall_ranking($student_class_id);

$position = null;
$total_ranked = count($ranking_data);

foreach ($ranking_data as $rank_row) {
    if ($rank_row->student_id == $child_id) {
        $position = $rank_row->position;
        break;
    }
}

// ✅ Growth (Term Comparison)
$growth = dubez_get_student_growth($child_id);

// ✅ Class Average (Comparison)
$class_average = 0;
if (!empty($ranking_data)) {
    $sum = 0;
    foreach ($ranking_data as $row) {
        $sum += $row->overall_average;
    }
    $class_average = round($sum / count($ranking_data), 1);
}


        // Attendance
        $attendance_records = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $attendance_table WHERE student_id = %d",
                $child_id
            )
        );

        $present_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $attendance_table WHERE student_id = %d AND status = 'present'",
                $child_id
            )
        );

        $attendance_rate = $attendance_records > 0 
            ? round(($present_count / $attendance_records) * 100, 1)
            : 0;

        $status_class = "safe";
        $status_text = "Stable";

        if ($average < 50 || $attendance_rate < 60) {
            $status_class = "risk";
            $status_text = "Attention Required";
        }

        

        // ✅ Billing Overview (Term-Aware)
        $billing_records = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT b.*, f.fee_name
                FROM {$wpdb->prefix}dubez_student_billing b
                JOIN {$wpdb->prefix}dubez_fee_structure f ON b.fee_structure_id = f.id
                WHERE b.student_id = %d
                AND b.academic_year = %s
                AND b.term = %s",
                $child_id,
                $context['year'],
                $context['term']
            )
        );

        $total_fees = 0;
        $total_paid = 0;

        foreach ($billing_records as $record) {
            $amount = $record->adjusted_amount ?: $record->original_amount;
            $total_fees += $amount;
            $total_paid += $record->amount_paid;
        }

        $total_outstanding = $total_fees - $total_paid;

        // ✅ Handle Payment Proof Submission
if (isset($_POST['dubez_submit_payment_proof'])) {

    check_admin_referer('dubez_payment_proof_nonce');

    $billing_id = intval($_POST['billing_id']);
    $amount_submitted = floatval($_POST['amount_submitted']);
    $reference_code = sanitize_text_field($_POST['reference_code']);

    if ($billing_id && $amount_submitted > 0 && !empty($_FILES['proof_file']['name'])) {

        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $uploaded = wp_handle_upload($_FILES['proof_file'], ['test_form' => false]);

        if (isset($uploaded['file'])) {

            $wpdb->insert(
                $wpdb->prefix . "dubez_payment_proofs",
                [
                    'student_id' => $child_id,
                    'billing_id' => $billing_id,
                    'amount_submitted' => $amount_submitted,
                    'reference_code' => $reference_code,
                    'proof_file' => $uploaded['url'],
                    'status' => 'pending'
                ],
                ['%d','%d','%f','%s','%s','%s']
            );

            echo '<div class="notice notice-success"><p>Payment proof submitted successfully. Awaiting verification.</p></div>';
        }
    }
}

        ?>

        <div class="parent-header-block">
            <div>
                <h1>Parent Monitoring Dashboard</h1>
                <p class="parent-subtitle">
                    Viewing Academic Progress for <?php echo esc_html(get_userdata($child_id)->display_name); ?>
                </p>
            </div>
            <div class="parent-session-badge">
                Oversight Mode
            </div>
        </div>
        <button class="mode-toggle"></button>

        <?php

$announcements = dubez_get_announcements_for_user($user->ID);
?>
<?php if (!empty($announcements)) : ?>
    <div class="parent-section" style="margin-top:20px;">
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

        <div class="parent-summary-status <?php echo esc_attr($status_class); ?>">
            <?php echo esc_html($status_text); ?>
        </div>

       <div class="parent-metrics">

    <div class="parent-metric-card">
        <h3>Overall Average</h3>
        <span class="metric-value"><?php echo esc_html($average); ?>%</span>
    </div>

    <div class="parent-metric-card">
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

    <div class="parent-metric-card">
        <h3>Class Average</h3>
        <span class="metric-value"><?php echo esc_html($class_average); ?>%</span>
    </div>

    <div class="parent-metric-card">
        <h3>Attendance Rate</h3>
        <span class="metric-value"><?php echo esc_html($attendance_rate); ?>%</span>
    </div>

    <div class="parent-metric-card">
        <h3>Growth</h3>
        <span class="metric-value">
            <?php 
            if ($growth['status'] === 'No Previous Term' || $growth['status'] === 'No Previous Data') {
                echo '<span style="color:#6b7280;">No Previous Data</span>';
            } else {
                $delta = $growth['delta'];
                if ($delta > 0) {
                    echo '<span style="color:#16a34a;">↑ +' . esc_html($delta) . '%</span>';
                } elseif ($delta < 0) {
                    echo '<span style="color:#dc2626;">↓ ' . esc_html($delta) . '%</span>';
                } else {
                    echo '<span style="color:#f59e0b;">→ 0%</span>';
                }
            }
            ?>
        </span>
    </div>

</div>




<!-- Phase 5 — Advanced Parent Intelligence Charts -->
<section class="parent-analytics-charts" style="margin-top:40px;">

    <h3 style="margin-bottom:20px;">Academic Performance Trends</h3>

    <div style="background:#ffffff;padding:20px;border:1px solid #e5e5e5;margin-bottom:30px;">
        <canvas id="termTrendChart" height="100"></canvas>
    </div>

    <h3 style="margin-bottom:20px;">Subject Performance (Current Term)</h3>

    <div style="background:#ffffff;padding:20px;border:1px solid #e5e5e5;margin-bottom:30px;">
        <canvas id="subjectTrendChart" height="100"></canvas>
    </div>

    <h3 style="margin-bottom:20px;">Attendance Trend</h3>

    <div style="background:#ffffff;padding:20px;border:1px solid #e5e5e5;">
        <canvas id="attendanceTrendChart" height="100"></canvas>
    </div>

</section>

<?php if (!empty($billing_records)) : ?>
    <div class="parent-section" style="margin-top:40px;">
        <h2>Financial Overview (<?php echo esc_html($context['term']); ?>)</h2>

        <div class="parent-metrics">

            <div class="parent-metric-card">
                <h3>Total Fees</h3>
                <span class="metric-value">
                    <?php echo number_format($total_fees, 2); ?>
                </span>
            </div>

            <div class="parent-metric-card">
                <h3>Amount Paid</h3>
                <span class="metric-value">
                    <?php echo number_format($total_paid, 2); ?>
                </span>
            </div>

            <div class="parent-metric-card">
                <h3>Outstanding</h3>
                <span class="metric-value" style="color:<?php echo $total_outstanding > 0 ? '#dc2626' : '#16a34a'; ?>">
                    <?php echo number_format($total_outstanding, 2); ?>
                </span>
            </div>

        </div>

        <table class="widefat fixed striped" style="margin-top:15px;">
            <thead>
                <tr>
                    <th>Fee</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($billing_records as $record) : 
                    $amount = $record->adjusted_amount ?: $record->original_amount;
                ?>
                    <tr>
                        <td><?php echo esc_html($record->fee_name); ?></td>
                        <td><?php echo number_format($amount, 2); ?></td>
                        <td><?php echo number_format($record->amount_paid, 2); ?></td>
                        <td><?php echo esc_html(ucfirst($record->status)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr>

<h2>Upload Payment Proof</h2>

<form method="POST" enctype="multipart/form-data">
    <?php wp_nonce_field('dubez_payment_proof_nonce'); ?>

    <input type="hidden" name="billing_id" value="<?php echo esc_attr($billing_records[0]->id ?? 0); ?>">

    <table class="form-table">
        <tr>
            <th>Amount Paid</th>
            <td>
                <input type="number" step="0.01" name="amount_submitted" required>
            </td>
        </tr>

        <tr>
            <th>Bank Reference</th>
            <td>
                <input type="text" name="reference_code">
            </td>
        </tr>

        <tr>
            <th>Upload Proof (Image/PDF)</th>
            <td>
                <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required>
            </td>
        </tr>
    </table>

    <p>
        <button type="submit" name="dubez_submit_payment_proof" class="button button-primary">
            Submit Payment Proof
        </button>
    </p>
</form>
    </div>
<?php endif; ?>

        <div class="parent-section">
            <h2>Academic Record</h2>
            <?php echo do_shortcode('[dubez_parent_view_child_grades]'); ?>
        </div>

    </div>
</section>

<?php get_footer(); ?>



