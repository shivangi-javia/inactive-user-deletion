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
    if (isset($_POST['iud_email_subject_first'])) {
        $iud_email_subject_first = sanitize_text_field($_POST['iud_email_subject_first']);
        update_option('iud_email_subject_first', $iud_email_subject_first);
    }
    if (isset($_POST['iud_email_message_first'])) {
        $iud_email_message_first = sanitize_textarea_field($_POST['iud_email_message_first']);
        update_option('iud_email_message_first', $iud_email_message_first);
    }
    if (isset($_POST['iud_email_subject_final'])) {
        $iud_email_subject_final = sanitize_text_field($_POST['iud_email_subject_final']);
        update_option('iud_email_subject_final', $iud_email_subject_final);
    }
    if (isset($_POST['iud_email_message_final'])) {
        $iud_email_message_final = sanitize_textarea_field($_POST['iud_email_message_final']);
        update_option('iud_email_message_final', $iud_email_message_final);
    }
    if (isset($_POST['iud_disable_emails']) && $_POST['iud_disable_emails'] == '1') {
        update_option('iud_disable_emails', true);
    } else {
        update_option('iud_disable_emails', false);
    }

    $iud_days_active = get_option('iud_days_active', 45); // Default to 45 days if not set
    $iud_warning_days_first = get_option('iud_warning_days_first', 3); // Default to 3 days before first warning if not set
    $iud_warning_days_final = get_option('iud_warning_days_final', 1); // Default to 1 day before final warning if not set
    $iud_email_subject_first = get_option('iud_email_subject_first', 'Warning: Your account will be deleted in 3 days due to inactivity');
    $iud_email_message_first = get_option('iud_email_message_first', "Dear {user_name},\n\nYour account on {site_name} ({site_url}) has been inactive for a while. If you do not log in within the next 3 days, your account will be deleted.\n\nBest regards,\nThe {site_name} Team");
    $iud_email_subject_final = get_option('iud_email_subject_final', 'Final Warning: Your account will be deleted in 1 day due to inactivity');
    $iud_email_message_final = get_option('iud_email_message_final', "Dear {user_name},\n\nThis is a final reminder that your account on {site_name} ({site_url}) will be deleted in 1 day due to inactivity. Please log in as soon as possible to prevent deletion.\n\nBest regards,\nThe {site_name} Team");

    $iud_disable_emails = get_option('iud_disable_emails', false);

    ?>
    <div class="wrap">
    <h2>Inactive User Deletion Settings</h2>
    <p><strong>Note:</strong> The following settings come with default values predefined in the code. You can override these values by modifying the settings below:</p>
    <ul>
        <li><strong>Inactive for 45 days</strong> before deletion (default value).</li>
        <li><strong>Warning emails</strong> are sent 3 days and 1 day before deletion (default values).</li>
    </ul>
    <p>Use the options below to configure the number of days a user can remain inactive before their account is deleted. You can also adjust when warning emails will be sent prior to deletion. Additionally, you can personalize the content of the first and final warning emails. If needed, you can disable these warning emails entirely.</p>
    
    <form method="POST">
        <table class="form-table">
            <tbody>
                <!-- Delete After Inactive Days and Disable Emails Section -->
                <tr>
                    <th><label for="iud_days_active">Delete After Inactive Days</label></th>
                    <td><input name="iud_days_active" id="iud_days_active" type="text" value="<?php echo esc_attr($iud_days_active); ?>" class="regular-text" /></td>
                </tr>
                
                <!-- First Warning Section -->
                <tr><th colspan="2"><h3>First Warning</h3></th></tr>
                <tr>
                    <th><label for="iud_warning_days_first">Days Before First Warning Email</label></th>
                    <td><input name="iud_warning_days_first" id="iud_warning_days_first" type="text" value="<?php echo esc_attr($iud_warning_days_first); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="iud_email_subject_first">Subject for First Warning Email</label></th>
                    <td><input name="iud_email_subject_first" id="iud_email_subject_first" type="text" value="<?php echo esc_attr($iud_email_subject_first); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="iud_email_message_first">Message for First Warning Email</label></th>
                    <td><textarea name="iud_email_message_first" id="iud_email_message_first" class="large-text"><?php echo esc_textarea($iud_email_message_first); ?></textarea></td>
                </tr>

                <!-- Final Warning Section -->
                <tr><th colspan="2"><h3>Final Warning</h3></th></tr>
                <tr>
                    <th><label for="iud_warning_days_final">Days Before Final Warning Email</label></th>
                    <td><input name="iud_warning_days_final" id="iud_warning_days_final" type="text" value="<?php echo esc_attr($iud_warning_days_final); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="iud_email_subject_final">Subject for Final Warning Email</label></th>
                    <td><input name="iud_email_subject_final" id="iud_email_subject_final" type="text" value="<?php echo esc_attr($iud_email_subject_final); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="iud_email_message_final">Message for Final Warning Email</label></th>
                    <td><textarea name="iud_email_message_final" id="iud_email_message_final" class="large-text"><?php echo esc_textarea($iud_email_message_final); ?></textarea></td>
                </tr>
                <!-- Disable Warning Emails Section -->
                <tr>
                    <th><label for="iud_disable_emails">Disable Warning Emails Before Deletion</label></th>
                    <td>
                        <input name="iud_disable_emails" id="iud_disable_emails" type="checkbox" value="1" <?php checked($iud_disable_emails, true); ?> />
                        <p class="description">Check this box to disable the sending of warning emails (both first and final) before the user’s account is deleted.</p>
                    </td>
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
    $iud_disable_emails = get_option('iud_disable_emails', false); // Disable emails if option is true

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
            if (!$iud_disable_emails) {
                if ($iud_days_inactive == $iud_days_active - $iud_warning_days_first) {
                    send_iud_warning_email($user, $iud_warning_days_first); // Send email first warning
                } elseif ($iud_days_inactive == $iud_days_active - $iud_warning_days_final) {
                    send_iud_warning_email($user, $iud_warning_days_final); // Send email final warning
                }
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

    $subject = $days_left == 3 ? get_option('iud_email_subject_first') : get_option('iud_email_subject_final');
    $message = $days_left == 3 ? get_option('iud_email_message_first') : get_option('iud_email_message_final');

    // Replace placeholders in message
    $message = str_replace('{user_name}', $user_name, $message);
    $message = str_replace('{site_name}', $site_name, $message);
    $message = str_replace('{site_url}', $site_url, $message);

    // Send the email
    wp_mail($user_email, $subject, $message);
}
?>
