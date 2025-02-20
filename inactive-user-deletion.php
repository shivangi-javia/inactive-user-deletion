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
 * Domain Path:         /languages
 * Requires at least:   5.0
 * Tested up to:        6.7
 * Requires PHP:        7.2
*/

// Include necessary WP files
require_once(ABSPATH . 'wp-admin/includes/user.php');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'inactive_user_deletion_activate');
register_deactivation_hook(__FILE__, 'inactive_user_deletion_deactivate');

// Activation hook function
function inactive_user_deletion_activate() {
    if (!wp_next_scheduled('inactive_user_deletion_cron')) {
        wp_schedule_event(time(), 'iud_every_day', 'inactive_user_deletion_cron');
    }
}

// Deactivation hook function
function inactive_user_deletion_deactivate() {
    $timestamp = wp_next_scheduled('inactive_user_deletion_cron');
    wp_unschedule_event($timestamp, 'inactive_user_deletion_cron');
}

// Register custom menu page
add_action('admin_menu', 'inactive_user_deletion_plugin_menu');
function inactive_user_deletion_plugin_menu() {
    add_menu_page(
        'Inactive User Deletion', 
        'Inactive User Deletion', 
        'manage_options', 
        'inactive-user-deletion', 
        'inactive_user_deletion_page', 
        'dashicons-trash'
    );
}

// Display settings page and save options
function inactive_user_deletion_page() {
    if (isset($_POST['iud_days_active'])) {
        $iud_days_active = sanitize_text_field($_POST['iud_days_active']);
        update_option('iud_days_active', $iud_days_active);
    }
    if (isset($_POST['iud_warning_days_first'])) {
        $iud_warning_days_first = sanitize_text_field($_POST['iud_warning_days_first']);
        update_option('iud_warning_days_first', $iud_warning_days_first);
    }
    if (isset($_POST['iud_warning_days_final'])) {
        $iud_warning_days_final = sanitize_text_field($_POST['iud_warning_days_final']);
        update_option('iud_warning_days_final', $iud_warning_days_final);
    }

    $iud_days_active = get_option('iud_days_active', 45); // Default to 45 days if not set
    $iud_warning_days_first = get_option('iud_warning_days_first', 3); // Default to 3 days before first warning if not set
    $iud_warning_days_final = get_option('iud_warning_days_final', 1); // Default to 1 day before final warning if not set

    ?>
    <div class="wrap">
        <h2>Inactive User Deletion Settings</h2>
        <p>Configure the number of days a user can be inactive before they are automatically deleted. If these settings are not configured, the defaults are:</p>
        <ul>
            <li><strong>Inactive for 45 days</strong> before deletion</li>
            <li><strong>Warning emails will be sent 3 days and 1 day before deletion</li>
        </ul>
        <p>To customize the settings, you can specify the number of days before deletion and the number of days before sending the warning emails below.</p>
        <form method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="iud_days_active">Delete After Inactive Days</label></th>
                        <td><input name="iud_days_active" id="iud_days_active" type="text" value="<?php echo esc_attr($iud_days_active); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="iud_warning_days_first">Days Before First Warning Email</label></th>
                        <td><input name="iud_warning_days_first" id="iud_warning_days_first" type="text" value="<?php echo esc_attr($iud_warning_days_first); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="iud_warning_days_final">Days Before Final Warning Email</label></th>
                        <td><input name="iud_warning_days_final" id="iud_warning_days_final" type="text" value="<?php echo esc_attr($iud_warning_days_final); ?>" class="regular-text" /></td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
            </p>
        </form>
    </div>
    <?php
}

// Capture user's last login time
add_action('wp_login', 'iud_capture_user_login_time', 10, 2);
function iud_capture_user_login_time($user_login, $user) {
    update_user_meta($user->ID, 'last_login', time());
}

// Add "Last Login" column to users list
add_filter('manage_users_columns', 'iud_add_last_login_column');
function iud_add_last_login_column($columns) {
    $columns['last_login'] = 'Last Login';
    return $columns;
}

add_filter('manage_users_custom_column', 'iud_display_last_login_column', 10, 3);
function iud_display_last_login_column($output, $column_id, $user_id) {
    if ($column_id == 'last_login') {
        $last_login = get_user_meta($user_id, 'last_login', true);
        $output = $last_login ? '<div title="Last login: ' . date('F j, Y, g:i a', $last_login) . '">' . human_time_diff($last_login) . '</div>' : 'No record';
    }
    return $output;
}

// Add a custom cron schedule for daily checks
add_filter('cron_schedules', 'iud_custom_daily_schedule');
function iud_custom_daily_schedule($schedules) {
    $schedules['iud_every_day'] = [
        'interval' => 86400, // 1 day in seconds
        'display'  => __('Every Day', 'inactive-user-deletion')
    ];
    return $schedules;
}

// Schedule the deletion task if it's not already scheduled
if (!wp_next_scheduled('inactive_user_deletion_cron')) {
    wp_schedule_event(time(), 'iud_every_day', 'inactive_user_deletion_cron');
}

// Hook the deletion task into the cron job
add_action('inactive_user_deletion_cron', 'iud_delete_inactive_users');
function iud_delete_inactive_users() {
    $iud_days_active = get_option('iud_days_active', 45); // Default to 45 days
    $iud_warning_days_first = get_option('iud_warning_days_first', 3); // Default to 3 days for first warning
    $iud_warning_days_final = get_option('iud_warning_days_final', 1); // Default to 1 day for final warning

    if (empty($iud_days_active)) {
        return; // No days set, no deletion needed
    }

    $users = get_users(['role__not_in' => ['Administrator']]);
    
    foreach ($users as $user) {
        $last_login = get_user_meta($user->ID, 'last_login', true);
        if ($last_login) {
            $last_active = date('F j, Y, g:i a', $last_login);
            $today = date('F j, Y, g:i a');
            $start = new DateTime($last_active);
            $end = new DateTime($today);

            $difference = $start->diff($end);
            $iud_days_inactive = $difference->days;

            // Check if the user is 3 or 1 day(s) before deletion
            if ($iud_days_inactive == $iud_days_active - $iud_warning_days_first) {
                send_iud_warning_email($user, $iud_warning_days_first); // Send email first warning
            } elseif ($iud_days_inactive == $iud_days_active - $iud_warning_days_final) {
                send_iud_warning_email($user, $iud_warning_days_final); // Send email final warning
            }

            // Delete user if inactive for the specified days
            if ($iud_days_inactive >= $iud_days_active) {
                wp_delete_user($user->ID);
            }
        }
    }
}

// Function to send inactivity warning email
function send_iud_warning_email($user, $days_left) {
    $user_email = $user->user_email;
    $user_name = $user->display_name;

    $site_name = get_bloginfo('name'); // Get the site name
    $site_url = get_bloginfo('url'); // Get the site URL

    if ($days_left == 3) {
        $subject = 'Warning: Your account will be deleted in 3 days due to inactivity';
        $message = "Dear $user_name,\n\nYour account on $site_name ($site_url) has been inactive for a while. If you do not log in within the next 3 days, your account will be deleted.\n\nBest regards,\nThe $site_name Team";
    } elseif ($days_left == 1) {
        $subject = 'Final Warning: Your account will be deleted in 1 day due to inactivity';
        $message = "Dear $user_name,\n\nThis is a final reminder that your account on $site_name ($site_url) will be deleted in 1 day due to inactivity. Please log in as soon as possible to prevent deletion.\n\nBest regards,\nThe $site_name Team";
    }

    // Send the email
    wp_mail($user_email, $subject, $message);
}
?>
