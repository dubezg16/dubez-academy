<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Current Academic Context
 */
function dubez_get_academic_context() {

    $year = get_option('dubez_current_academic_year', date('Y'));
    $term = get_option('dubez_current_term', '1st Term');

    return array(
        'year' => $year,
        'term' => $term
    );
}

/**
 * Admin Academic Context Page
 */
function dubez_render_academic_context_page() {

    if (!current_user_can('dubez_manage_context')) {
        wp_die('Unauthorized access.');
    }

    if (isset($_POST['dubez_context_submit'])) {

        check_admin_referer('dubez_context_action');

        update_option(
            'dubez_current_academic_year',
            sanitize_text_field($_POST['academic_year'])
        );

        update_option(
            'dubez_current_term',
            sanitize_text_field($_POST['current_term'])
        );

        echo '<div class="notice notice-success"><p>Academic context updated successfully.</p></div>';
    }

    $context = dubez_get_academic_context();
    ?>

    <div class="wrap">
        <h1>Academic Context</h1>

        <form method="post">
            <?php wp_nonce_field('dubez_context_action'); ?>

            <table class="form-table">
                <tr>
                    <th>Academic Year</th>
                    <td>
                        <input type="text" name="academic_year" value="<?php echo esc_attr($context['year']); ?>" />
                    </td>
                </tr>

                <tr>
                    <th>Current Term</th>
                    <td>
                        <select name="current_term">
                            <option value="1st Term" <?php selected($context['term'], '1st Term'); ?>>1st Term</option>
                            <option value="2nd Term" <?php selected($context['term'], '2nd Term'); ?>>2nd Term</option>
                            <option value="3rd Term" <?php selected($context['term'], '3rd Term'); ?>>3rd Term</option>
                        </select>
                    </td>
                </tr>
            </table>

            <p>
                <input type="submit" name="dubez_context_submit" class="button button-primary" value="Save Context">
            </p>
        </form>
    </div>

    <?php
}

/**
 * Register Admin Menu
 */
