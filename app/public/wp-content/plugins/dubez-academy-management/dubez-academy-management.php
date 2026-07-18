<?php
/*
Plugin Name: Dubez Academy Management
Description: Institutional Academic ERP Core System
Version: 1.0
Author: Dubez Academy
*/

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core Loader
 */

define('DUBEZ_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DUBEZ_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load Core Modules
 */

require_once DUBEZ_PLUGIN_PATH . 'inc/roles.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/academic-context.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/submissions.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/billing.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/attendance.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/messaging.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/alerts.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/helpers.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/ranking.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/parent-analytics.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/teacher.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/admin-control-center.php';
require_once DUBEZ_PLUGIN_PATH . 'inc/export.php';

/**
 * Register Chart Scripts
 */
function dubez_register_chart_scripts() {

    wp_register_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        array(),
        '4.4.0',
        true
    );

    wp_register_script(
        'dubez-parent-charts',
        DUBEZ_PLUGIN_URL . 'js/parent-charts.js',
        array('chart-js'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'dubez_register_chart_scripts');