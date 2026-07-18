<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================
   ADMIN CONTROL CENTER MENU
========================================== */

function dubez_register_admin_control_center() {

    add_menu_page(
        'Dubez Control Center',
        'Dubez Academy',
        'administrator',
        'dubez-control-center',
        'dubez_render_admin_overview',
        'dashicons-chart-line',
        2
    );

    add_submenu_page(
        'dubez-control-center',
        'Academic Structure',
        'Academic Structure',
        'administrator',
        'dubez-academic-structure',
        'dubez_render_academic_structure'
    );

    add_submenu_page(
        'dubez-control-center',
        'Governance',
        'Governance',
        'administrator',
        'dubez-governance',
        'dubez_render_governance_panel'
    );

    add_submenu_page(
        'dubez-control-center',
        'Attendance',
        'Attendance',
        'administrator',
        'dubez-attendance-admin',
        'dubez_render_attendance_panel'
    );

    add_submenu_page(
        'dubez-control-center',
        'Finance',
        'Finance',
        'administrator',
        'dubez-finance-admin',
        'dubez_render_finance_panel'
    );

    add_submenu_page(
        'dubez-control-center',
        'Risk Engine',
        'Risk Engine',
        'administrator',
        'dubez-risk-engine',
        'dubez_render_risk_panel'
    );

    add_submenu_page(
        'dubez-control-center',
        'Audit Log',
        'Audit Log',
        'administrator',
        'dubez-audit-log',
        'dubez_render_audit_panel'
    );
}

add_action('admin_menu', 'dubez_register_admin_control_center');


/* ==========================================
   ADMIN PANEL RENDER FUNCTIONS
========================================== */

function dubez_render_admin_overview() {
    if (isset($_POST['dubez_update_context'])) {

    if (
        isset($_POST['dubez_context_nonce']) &&
        wp_verify_nonce($_POST['dubez_context_nonce'],'dubez_context_action')
    ) {
        update_option('dubez_current_academic_year', sanitize_text_field($_POST['academic_year']));
        update_option('dubez_current_term', sanitize_text_field($_POST['current_term']));
    }
}
    echo '<div class="premium-table-wrapper">';
echo '<h2>Academic Context Control</h2>';

echo '<form method="post">';
wp_nonce_field('dubez_context_action','dubez_context_nonce');

echo '<select name="academic_year">';
for ($y = date('Y')-2; $y <= date('Y')+2; $y++) {
    $selected = ($context['year'] == $y) ? 'selected' : '';
    echo "<option value='{$y}' {$selected}>{$y}</option>";
}
echo '</select>';

echo '<select name="current_term">';
$terms = ['1st Term','2nd Term','3rd Term'];
foreach ($terms as $term) {
    $selected = ($context['term'] == $term) ? 'selected' : '';
    echo "<option value='{$term}' {$selected}>{$term}</option>";
}
echo '</select>';

echo '<button type="submit" name="dubez_update_context" class="button button-primary">
Update Context
</button>';

echo '</form>';
echo '</div>';

    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    global $wpdb;
    $context = dubez_get_academic_context();
    /* ===============================
   HANDLE TERM LOCK TOGGLE
=============================== */

if (isset($_POST['toggle_term_lock'])) {

    if (
        isset($_POST['dubez_lock_nonce']) &&
        wp_verify_nonce($_POST['dubez_lock_nonce'], 'dubez_lock_action')
    ) {

        $current = get_option('dubez_term_locked', false);
        update_option('dubez_term_locked', !$current);

        dubez_log_action('term_lock_toggle', !$current ? 'Term locked' : 'Term unlocked');
    }
}


    
    $locked = dubez_is_term_locked();

echo '<div style="margin-top:20px;">';
echo '<strong>Term Lock Status:</strong> ' . ($locked ? '<span style="color:#b91c1c;">LOCKED</span>' : '<span style="color:#16a34a;">OPEN</span>');
echo '</div>';

echo '<form method="post" style="margin-top:10px;">';
wp_nonce_field('dubez_lock_action','dubez_lock_nonce');
echo '<button type="submit" name="toggle_term_lock" class="button button-secondary">';
echo $locked ? 'Unlock Term' : 'Lock Term';
echo '</button>';
echo '</form>';

    /* ============================
       ACADEMIC METRICS
    ============================ */

    $total_students = count(get_users(['role'=>'student']));
    $total_teachers = count(get_users(['role'=>'teacher']));
    $total_classes  = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dubez_classes");

    $pass_rate = $wpdb->get_var(
        "SELECT 
            (SUM(CASE WHEN numeric_score >= 50 THEN 1 ELSE 0 END) /
             COUNT(numeric_score)) * 100
         FROM {$wpdb->prefix}dubez_submissions
         WHERE numeric_score IS NOT NULL"
    );

    $pass_rate = $pass_rate ? round($pass_rate,1) : 0;

    $at_risk_count = $wpdb->get_var(
        "SELECT COUNT(DISTINCT student_id)
         FROM {$wpdb->prefix}dubez_submissions
         WHERE numeric_score < 50"
    );

    /* ============================
       ATTENDANCE METRICS
    ============================ */

    $attendance_rate = $wpdb->get_var(
        "SELECT 
            (SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) /
             COUNT(*)) * 100
         FROM {$wpdb->prefix}dubez_attendance"
    );

    $attendance_rate = $attendance_rate ? round($attendance_rate,1) : 0;

    /* ============================
       FINANCIAL METRICS
    ============================ */

    $total_billing = $wpdb->get_var("SELECT SUM(original_amount) FROM {$wpdb->prefix}dubez_student_billing");
    $total_paid    = $wpdb->get_var("SELECT SUM(amount_paid) FROM {$wpdb->prefix}dubez_student_billing");
    $outstanding   = $total_billing - $total_paid;

    ?>

    <div class="matrix-wrapper">

        <div class="matrix-header">
            <div>
                <h1>Institutional Command Center</h1>
                <p>Academic Year: <?php echo esc_html($context['year']); ?> | Term: <?php echo esc_html($context['term']); ?></p>
            </div>
            <button id="modeToggle" class="mode-toggle">
                <span id="modeIcon">🌙</span>
            </button>
        </div>

        <div class="matrix-grid">

            <div class="matrix-card">
                <div class="matrix-label">Students</div>
                <div class="matrix-value"><?php echo $total_students; ?></div>
                <div class="matrix-meta">Active enrollment</div>
            </div>

            <div class="matrix-card">
                <div class="matrix-label">Teachers</div>
                <div class="matrix-value"><?php echo $total_teachers; ?></div>
                <div class="matrix-meta">Academic staff</div>
            </div>

            <div class="matrix-card">
                <div class="matrix-label">Classes</div>
                <div class="matrix-value"><?php echo $total_classes; ?></div>
                <div class="matrix-meta">Structured cohorts</div>
            </div>

            <div class="matrix-card">
                <div class="matrix-label">Pass Rate</div>
                <div class="matrix-value"><?php echo $pass_rate; ?>%</div>
                <div class="matrix-meta">Academic success ratio</div>
            </div>

            <div class="matrix-card alert-card">
                <div class="matrix-label">At‑Risk Students</div>
                <div class="matrix-value"><?php echo $at_risk_count; ?></div>
                <div class="matrix-meta">Below performance threshold</div>
            </div>

            <div class="matrix-card">
                <div class="matrix-label">Attendance</div>
                <div class="matrix-value"><?php echo $attendance_rate; ?>%</div>
                <div class="matrix-meta">Institutional average</div>
            </div>

            <div class="matrix-card">
                <div class="matrix-label">Total Billing</div>
                <div class="matrix-value"><?php echo number_format($total_billing,2); ?></div>
                <div class="matrix-meta">Financial volume</div>
            </div>

            <div class="matrix-card alert-card">
                <div class="matrix-label">Outstanding</div>
                <div class="matrix-value"><?php echo number_format($outstanding,2); ?></div>
                <div class="matrix-meta">Pending collections</div>
            </div>

        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const body = document.body;
        const toggle = document.getElementById('modeToggle');
        const icon = document.getElementById('modeIcon');

        let mode = localStorage.getItem('mode') || 'dark';
        body.classList.add(mode + '-mode');
        icon.textContent = mode === 'dark' ? '☀️' : '🌙';

        toggle.addEventListener('click', function(){
            body.classList.toggle('dark-mode');
            body.classList.toggle('light-mode');
            mode = body.classList.contains('dark-mode') ? 'dark' : 'light';
            localStorage.setItem('mode', mode);
            icon.textContent = mode === 'dark' ? '☀️' : '🌙';
        });
    });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function(){
    const counters = document.querySelectorAll('.matrix-value');
    counters.forEach(counter => {
        const target = parseFloat(counter.innerText.replace(/,/g,''));
        let count = 0;
        const increment = target / 40;

        const update = () => {
            count += increment;
            if (count < target) {
                counter.innerText = Math.floor(count);
                requestAnimationFrame(update);
            } else {
                counter.innerText = target;
            }
        };

        update();
    });
});
</script>

    <?php
}

function dubez_render_academic_structure() {

    global $wpdb;

    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    $message = '';

    /* ======================================
       HANDLE CLASS CREATION
    ====================================== */

    if (isset($_POST['dubez_create_class'])) {

        if (
            !isset($_POST['dubez_class_nonce']) ||
            !wp_verify_nonce($_POST['dubez_class_nonce'], 'dubez_class_action')
        ) {
            $message = '<div class="notice notice-error"><p>Security verification failed.</p></div>';
        } else {

            $class_name = sanitize_text_field($_POST['class_name']);
            $academic_year = sanitize_text_field($_POST['academic_year']);

            if ($class_name && $academic_year) {
                $wpdb->insert(
                    $wpdb->prefix . 'dubez_classes',
                    [
                        'class_name' => $class_name,
                        'academic_year' => $academic_year
                    ],
                    ['%s','%s']
                );

                dubez_log_action('class_created', 'Created class: ' . $class_name);

                $message = '<div class="notice notice-success"><p>Class created successfully.</p></div>';
            }

        }
    }

    /* ======================================
       HANDLE SUBJECT CREATION
    ====================================== */

    if (isset($_POST['dubez_create_subject'])) {

        if (
            !isset($_POST['dubez_subject_nonce']) ||
            !wp_verify_nonce($_POST['dubez_subject_nonce'], 'dubez_subject_action')
        ) {
            $message = '<div class="notice notice-error"><p>Security verification failed.</p></div>';
        } else {

            $subject_name = sanitize_text_field($_POST['subject_name']);
            $class_id = intval($_POST['class_id']);
            $academic_year = sanitize_text_field($_POST['subject_year']);

            if ($subject_name && $class_id) {

                $wpdb->insert(
                    $wpdb->prefix . 'dubez_subjects',
                    [
                        'subject_name' => $subject_name,
                        'class_id' => $class_id,
                        'academic_year' => $academic_year
                    ],
                    ['%s','%d','%s']
                );

                dubez_log_action('subject_created', 'Created subject: ' . $subject_name);

                $message = '<div class="notice notice-success"><p>Subject created successfully.</p></div>';
            }

        }
    }

    /* ======================================
       HANDLE TEACHER ASSIGNMENT
    ====================================== */

    if (isset($_POST['dubez_assign_teacher'])) {

        if (
            !isset($_POST['dubez_assign_nonce']) ||
            !wp_verify_nonce($_POST['dubez_assign_nonce'], 'dubez_assign_action')
        ) {
            $message = '<div class="notice notice-error"><p>Security verification failed.</p></div>';
        } else {

            $teacher_id = intval($_POST['teacher_id']);
            $assign_class_id = intval($_POST['assign_class_id']);

            update_user_meta($teacher_id, 'teacher_class_id', $assign_class_id);

            dubez_log_action('teacher_assigned', 'Assigned teacher ID '.$teacher_id.' to class ID '.$assign_class_id);

            $message = '<div class="notice notice-success"><p>Teacher assigned successfully.</p></div>';

        }
    }

    /* ======================================
       FETCH DATA
    ====================================== */

    $total_classes  = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dubez_classes");
    $total_subjects = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dubez_subjects");
    $total_teachers = count(get_users(['role'=>'teacher']));
    $total_students = count(get_users(['role'=>'student']));

    $classes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dubez_classes");
    $subjects = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dubez_subjects");
    $teachers = get_users(['role'=>'teacher']);

    echo '<div class="wrap">';
    echo '<h1>Academic Structure Management</h1>';
    echo $message;

    /* ======================================
       SUMMARY CARDS
    ====================================== */

    echo '<div class="structure-summary-grid">
        <div class="structure-card bg-green">
            <div class="structure-title">Classes</div>
            <div class="structure-value">'.$total_classes.'</div>
        </div>

        <div class="structure-card bg-blue">
            <div class="structure-title">Subjects</div>
            <div class="structure-value">'.$total_subjects.'</div>
        </div>

        <div class="structure-card bg-orange">
            <div class="structure-title">Teachers</div>
            <div class="structure-value">'.$total_teachers.'</div>
        </div>

        <div class="structure-card bg-red">
            <div class="structure-title">Students</div>
            <div class="structure-value">'.$total_students.'</div>
        </div>
    </div>';

    /* ======================================
       CONTROL PANELS
    ====================================== */

    echo '<div class="structure-panels">';

    /* ==== LEFT PANEL ==== */
    echo '<div class="structure-panel">';
    echo '<h2>Create Class</h2>';

    echo '<form method="post">';
    wp_nonce_field('dubez_class_action','dubez_class_nonce');
    echo '<input type="text" name="class_name" placeholder="Class Name" required><br><br>';
    echo '<input type="text" name="academic_year" placeholder="Academic Year" required><br><br>';
    echo '<button type="submit" name="dubez_create_class" class="button button-primary">Create Class</button>';
    echo '</form>';

    echo '<hr><h3>Existing Classes</h3>';
    echo '<ul class="structure-list">';
    foreach ($classes as $class) {
        echo '<li>'.$class->class_name.' ('.$class->academic_year.')</li>';
    }
    echo '</ul>';
    echo '</div>';

    /* ==== RIGHT PANEL ==== */
    echo '<div class="structure-panel">';
    echo '<h2>Create Subject</h2>';

    echo '<form method="post">';
    wp_nonce_field('dubez_subject_action','dubez_subject_nonce');

    echo '<input type="text" name="subject_name" placeholder="Subject Name" required><br><br>';

    echo '<select name="class_id" required>';
    echo '<option value="">Select Class</option>';
    foreach ($classes as $class) {
        echo '<option value="'.$class->id.'">'.$class->class_name.'</option>';
    }
    echo '</select><br><br>';

    echo '<input type="text" name="subject_year" placeholder="Academic Year" required><br><br>';

    echo '<button type="submit" name="dubez_create_subject" class="button button-primary">Create Subject</button>';
    echo '</form>';

    echo '<hr><h3>Assign Teacher</h3>';

    echo '<form method="post">';
    wp_nonce_field('dubez_assign_action','dubez_assign_nonce');

    echo '<select name="teacher_id" required>';
    echo '<option value="">Select Teacher</option>';
    foreach ($teachers as $teacher) {
        echo '<option value="'.$teacher->ID.'">'.$teacher->display_name.'</option>';
    }
    echo '</select><br><br>';

    echo '<select name="assign_class_id" required>';
    echo '<option value="">Select Class</option>';
    foreach ($classes as $class) {
        echo '<option value="'.$class->id.'">'.$class->class_name.'</option>';
    }
    echo '</select><br><br>';

    echo '<button type="submit" name="dubez_assign_teacher" class="button button-primary">Assign Teacher</button>';
    echo '</form>';

    echo '</div>'; // right panel

    echo '</div>'; // panels
    echo '</div>';
}

function dubez_render_governance_panel() {

    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $context = dubez_get_academic_context();
    $message = '';
    $filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

    /* ============================
       HANDLE GRADE OVERRIDE
    ============================ */

    if (isset($_POST['dubez_override_grade'])) {

        if (
            isset($_POST['dubez_override_nonce']) &&
            wp_verify_nonce($_POST['dubez_override_nonce'], 'dubez_override_action')
        ) {

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
                [
                    'numeric_score' => $numeric_score,
                    'grade_letter'  => $grade_letter
                ],
                ['id' => $submission_id],
                ['%d','%s'],
                ['%d']
            );

            dubez_log_action(
                'grade_override',
                'Submission ID '.$submission_id.' overridden to '.$numeric_score
            );

            $message = '<div class="notice notice-success">Grade overridden successfully.</div>';
        }
    }

    /* ============================
       EXPORT CSV
    ============================ */

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {

        $rows = $wpdb->get_results(
            "SELECT u.display_name, p.post_title, s.numeric_score, s.grade_letter
             FROM {$wpdb->prefix}dubez_submissions s
             JOIN {$wpdb->users} u ON s.student_id = u.ID
             JOIN {$wpdb->posts} p ON s.assignment_id = p.ID",
            ARRAY_N
        );

        dubez_export_csv(
            'governance_report.csv',
            ['Student','Assignment','Score','Grade'],
            $rows
        );
    }

    /* ============================
       FETCH SUBMISSIONS
    ============================ */

    $where = ($filter_status === 'ungraded') ? "WHERE s.numeric_score IS NULL" : "";

    $submissions = $wpdb->get_results(
        "SELECT s.*, u.display_name, p.post_title
         FROM {$wpdb->prefix}dubez_submissions s
         JOIN {$wpdb->users} u ON s.student_id = u.ID
         JOIN {$wpdb->posts} p ON s.assignment_id = p.ID
         $where
         ORDER BY s.submission_date DESC"
    );

    echo '<div class="wrap">';
    echo '<h1>Academic Governance</h1>';
    echo $message;

    echo '<div style="margin-bottom:20px;">
            <a href="?page=dubez-governance">All</a> |
            <a href="?page=dubez-governance&status=ungraded">Ungraded</a> |
            <a href="?page=dubez-governance&export=csv">Export CSV</a>
          </div>';

    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Student</th>
                <th>Assignment</th>
                <th>Score</th>
                <th>Override</th>
            </tr>
          </thead><tbody>';

    foreach ($submissions as $row) {

        echo '<tr>';
        echo '<td>'.esc_html($row->display_name).'</td>';
        echo '<td>'.esc_html($row->post_title).'</td>';
        echo '<td>'.esc_html($row->numeric_score).' ('.$row->grade_letter.')</td>';
        echo '<td>
                <form method="post">
                    '.wp_nonce_field('dubez_override_action','dubez_override_nonce', true, false).'
                    <input type="hidden" name="submission_id" value="'.$row->id.'">
                    <input type="number" name="numeric_score" min="0" max="100" required>
                    <button type="submit" name="dubez_override_grade" class="button button-secondary">
                        Override
                    </button>
                </form>
              </td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}
function dubez_render_attendance_panel() {

    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    global $wpdb;
    $context = dubez_get_academic_context();

    $attendance_summary = $wpdb->get_results(
        "SELECT 
            um.meta_value as class_id,
            COUNT(*) as total_records,
            SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) as present_count
         FROM {$wpdb->prefix}dubez_attendance a
         JOIN {$wpdb->prefix}usermeta um
            ON a.student_id = um.user_id
         WHERE um.meta_key='student_class_id'
         GROUP BY um.meta_value"
    );

    echo '<div class="matrix-wrapper">';
    echo '<h1>Attendance Governance</h1>';
    echo '<p class="admin-subtext">Term: '.$context['term'].'</p>';

    echo '<div class="matrix-grid">';

    foreach ($attendance_summary as $row) {

        $rate = $row->total_records > 0
            ? round(($row->present_count / $row->total_records) * 100,1)
            : 0;

        echo '<div class="matrix-card">
                <div class="matrix-label">Class '.$row->class_id.'</div>
                <div class="matrix-value">'.$rate.'%</div>
                <div class="matrix-meta">'.$row->present_count.' of '.$row->total_records.' Present</div>
              </div>';
    }

    echo '</div>';

    echo '<div class="premium-chart-wrapper">';
    echo '<canvas id="attendanceChart" height="100"></canvas>';
    echo '</div>';

    echo '
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function(){

        const ctx = document.getElementById("attendanceChart");

        if (!ctx) return;

        new Chart(ctx, {
            type: "line",
            data: {
                labels: ['.implode(',', array_map(function($r){ return "'Class ".$r->class_id."'"; }, $attendance_summary)).'],
                datasets: [{
                    label: "Attendance %",
                    data: ['.implode(',', array_map(function($r){
                        return $r->total_records > 0
                            ? round(($r->present_count / $r->total_records) * 100,1)
                            : 0;
                    }, $attendance_summary)).'],
                    borderColor: "#16a34a",
                    backgroundColor: "rgba(22,163,74,0.1)",
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display:false } },
                scales: { y: { beginAtZero:true, max:100 } }
            }
        });

    });
    </script>';

    echo '</div>';
}
function dubez_render_finance_panel() {

    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $context = dubez_get_academic_context();
    $message = '';

    /* ============================
       HANDLE PAYMENT APPROVAL
    ============================ */

    if (isset($_POST['dubez_approve_payment'])) {

        if (
            isset($_POST['dubez_payment_nonce']) &&
            wp_verify_nonce($_POST['dubez_payment_nonce'], 'dubez_payment_action')
        ) {

            $billing_id = intval($_POST['billing_id']);
            $amount     = floatval($_POST['amount']);

            $wpdb->update(
                $wpdb->prefix . 'dubez_student_billing',
                [
                    'amount_paid' => $amount,
                    'status'      => ($amount > 0 ? 'partial' : 'unpaid')
                ],
                ['id' => $billing_id],
                ['%f','%s'],
                ['%d']
            );

            dubez_log_action(
                'finance_override',
                'Billing ID '.$billing_id.' updated to '.$amount
            );

            $message = '<div class="notice notice-success">Payment updated.</div>';
        }
    }

    /* ============================
       EXPORT CSV
    ============================ */

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {

        $rows = $wpdb->get_results(
            "SELECT u.display_name, b.original_amount, b.amount_paid
             FROM {$wpdb->prefix}dubez_student_billing b
             JOIN {$wpdb->users} u ON b.student_id = u.ID",
            ARRAY_N
        );

        dubez_export_csv(
            'finance_report.csv',
            ['Student','Total','Paid'],
            $rows
        );
    }

    $records = $wpdb->get_results(
        "SELECT b.*, u.display_name
         FROM {$wpdb->prefix}dubez_student_billing b
         JOIN {$wpdb->users} u ON b.student_id = u.ID"
    );

    echo '<div class="wrap">';
    echo '<h1>Financial Governance</h1>';
    echo $message;

    echo '<a href="?page=dubez-finance-admin&export=csv" class="button">Export CSV</a>';

    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Student</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Status</th>
                <th>Adjust</th>
            </tr>
          </thead><tbody>';

    foreach ($records as $row) {

        $status = ($row->original_amount - $row->amount_paid)<=0?'Paid':'Unpaid';

        echo '<tr>
                <td>'.$row->display_name.'</td>
                <td>'.$row->original_amount.'</td>
                <td>'.$row->amount_paid.'</td>
                <td>'.$status.'</td>
                <td>
                    <form method="post">
                        '.wp_nonce_field('dubez_payment_action','dubez_payment_nonce', true, false).'
                        <input type="hidden" name="billing_id" value="'.$row->id.'">
                        <input type="number" step="0.01" name="amount" required>
                        <button type="submit" name="dubez_approve_payment" class="button button-secondary">
                            Update
                        </button>
                    </form>
                </td>
              </tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}
if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    global $wpdb;
    $context = dubez_get_academic_context();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=risk_report.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student','Average','Attendance','Outstanding','Risk']);

    $students = get_users(['role'=>'student']);

    foreach ($students as $student) {

        $student_id = $student->ID;

        $avg = $wpdb->get_var(
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

        $avg = $avg ? round($avg,1) : 0;

        $attendance_total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}dubez_attendance
                 WHERE student_id = %d
                 AND academic_year = %s
                 AND term = %s",
                $student_id,
                $context['year'],
                $context['term']
            )
        );

        $attendance_present = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}dubez_attendance
                 WHERE student_id = %d
                 AND academic_year = %s
                 AND term = %s
                 AND status = 'present'",
                $student_id,
                $context['year'],
                $context['term']
            )
        );

        $attendance_rate = $attendance_total > 0
            ? round(($attendance_present / $attendance_total) * 100,1)
            : 0;

        $billing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(original_amount - amount_paid)
                 FROM {$wpdb->prefix}dubez_student_billing
                 WHERE student_id = %d
                 AND academic_year = %s
                 AND term = %s",
                $student_id,
                $context['year'],
                $context['term']
            )
        );

        $billing = $billing ? $billing : 0;

        $risk = 'Stable';
        if ($avg < 50 || $attendance_rate < 60 || $billing > 0) $risk = 'Warning';
        if ($avg < 40 || $attendance_rate < 50) $risk = 'Critical';

        fputcsv($output, [
            $student->display_name,
            $avg,
            $attendance_rate,
            $billing,
            $risk
        ]);
    }

    fclose($output);
    exit;
}

function dubez_render_risk_panel() {

    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    global $wpdb;
    $context = dubez_get_academic_context();

    $students = get_users(['role'=>'student']);

    $stable = 0;
    $warning = 0;
    $critical = 0;

    $risk_data = [];

    foreach ($students as $student) {

        $student_id = $student->ID;

        $avg = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(numeric_score)
                 FROM {$wpdb->prefix}dubez_submissions
                 WHERE student_id = %d",
                $student_id
            )
        );

        $avg = $avg ? round($avg,1) : 0;

        $risk = 'Stable';
        $color = '#16a34a';

        if ($avg < 50) {
            $risk = 'Warning';
            $color = '#d97706';
            $warning++;
        }

        if ($avg < 40) {
            $risk = 'Critical';
            $color = '#b91c1c';
            $critical++;
        }

        if ($risk === 'Stable') {
            $stable++;
        }

        $risk_data[] = [
            'name' => $student->display_name,
            'avg' => $avg,
            'risk' => $risk,
            'color' => $color
        ];
    }

    echo '<div class="matrix-wrapper">';
    echo '<h1>Risk Engine Monitoring</h1>';
    echo '<p class="admin-subtext">Academic Year: '.$context['year'].' | Term: '.$context['term'].'</p>';

    echo '<div class="matrix-grid">

        <div class="matrix-card">
            <div class="matrix-label">Stable</div>
            <div class="matrix-value">'.$stable.'</div>
        </div>

        <div class="matrix-card alert-card">
            <div class="matrix-label">Warning</div>
            <div class="matrix-value">'.$warning.'</div>
        </div>

        <div class="matrix-card alert-card">
            <div class="matrix-label">Critical</div>
            <div class="matrix-value">'.$critical.'</div>
        </div>

    </div>';

    echo '<div class="premium-chart-wrapper">';
    echo '<canvas id="riskChart" height="100"></canvas>';
    echo '</div>';

    echo '
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function(){

        const ctx = document.getElementById("riskChart");

        if (!ctx) return;

        new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Stable","Warning","Critical"],
                datasets: [{
                    data: ['.$stable.','.$warning.','.$critical.'],
                    backgroundColor: ["#16a34a","#d97706","#b91c1c"]
                }]
            },
            options: {
                plugins: { legend: { position:"bottom" } }
            }
        });

    });
    </script>';

    echo '<div class="premium-table-wrapper">';
    echo '<h2>Student Risk Details</h2>';
    echo '<table class="premium-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Average</th>
                    <th>Status</th>
                </tr>
            </thead><tbody>';

    foreach ($risk_data as $row) {

        echo '<tr>
                <td>'.$row['name'].'</td>
                <td>'.$row['avg'].'%</td>
                <td style="color:'.$row['color'].'; font-weight:600;">'.$row['risk'].'</td>
              </tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

    echo '</div>';
}

function dubez_render_audit_panel() {

    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $logs = $wpdb->get_results(
        "SELECT a.*, u.display_name
         FROM {$wpdb->prefix}dubez_audit_log a
         LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
         ORDER BY a.id DESC
         LIMIT 100"
    );

    echo '<div class="wrap">';
    echo '<h1>Audit Log</h1>';

    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>User</th>
                <th>Action</th>
                <th>Old Value</th>
                <th>New Value</th>
                <th>Term</th>
                <th>Date</th>
            </tr>
          </thead><tbody>';

    foreach ($logs as $log) {

        echo '<tr>
                <td>'.esc_html($log->display_name).'</td>
                <td>'.esc_html($log->action_type).'</td>
                <td>'.esc_html($log->old_value).'</td>
                <td>'.esc_html($log->new_value).'</td>
                <td>'.esc_html($log->term).'</td>
                <td>'.esc_html($log->created_at).'</td>
              </tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}
/* ==========================================
   ADMIN CSS ENQUEUE
========================================== */

function dubez_admin_styles($hook) {

    if (strpos($hook, 'dubez') === false) {
        return;
    }

wp_enqueue_style(
    'dubez-admin-style',
    DUBEZ_PLUGIN_URL . 'assets/admin.css',
    array(),
    '1.0.0'
);

wp_enqueue_script(
    'dubez-admin-mode',
    DUBEZ_PLUGIN_URL . 'assets/admin-mode.js',
    array(),
    '1.0.0',
    true
);
}

add_action('admin_enqueue_scripts', 'dubez_admin_styles');

