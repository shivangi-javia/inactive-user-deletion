<?php
/*
 * Plugin Name:         Inactive User Deletion
 * Plugin URI:          https://github.com/shivangi-javia/inactive-user-deletion/
 * Description:         Automatically deletes users inactive for a set number of days.
 * Version:             1.0
 * Author:              Shivangee Javia
 * Author URI:          https://profiles.wordpress.org/shivangijavia2106/
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:         inactive-user-deletion
 * Requires at least:   5.0
 * Tested up to:        6.7
 * Requires PHP:        7.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include other plugin files
require_once( plugin_dir_path( __FILE__ ) . 'includes/admin-menu.php');
require_once( plugin_dir_path( __FILE__ ) . 'includes/user-management.php');
require_once( plugin_dir_path( __FILE__ ) . 'includes/utility.php');
require_once( plugin_dir_path( __FILE__ ) . 'includes/email-functions.php');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'iaud_inactive_user_deletion_activate');
register_deactivation_hook(__FILE__, 'iaud_inactive_user_deletion_deactivate');

// Activation hook function
function iaud_inactive_user_deletion_activate() {
    if (!wp_next_scheduled('iaud_inactive_user_deletion_cron')) {
        wp_schedule_event(time(), 'iaud_every_day', 'iaud_inactive_user_deletion_cron');
    }
}

// Deactivation hook function
function iaud_inactive_user_deletion_deactivate() {
    $timestamp = wp_next_scheduled('iaud_inactive_user_deletion_cron');
    wp_unschedule_event($timestamp, 'iaud_inactive_user_deletion_cron');
}

// Add custom cron schedule
add_filter('cron_schedules', 'iaud_custom_daily_schedule');
function iaud_custom_daily_schedule($schedules) {
    $schedules['iaud_every_day'] = [
        'interval' => 86400, // 1 day in seconds
        'display'  => __('Every Day', 'inactive-user-deletion')
    ];
    return $schedules;
}

// Hook the deletion task into the cron job
add_action('iaud_inactive_user_deletion_cron', 'iaud_delete_inactive_users');
?>