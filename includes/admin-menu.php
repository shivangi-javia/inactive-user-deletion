<?php

// Register custom menu page
add_action('admin_menu', 'iaud_inactive_user_deletion_plugin_menu');
function iaud_inactive_user_deletion_plugin_menu() {
    add_menu_page(
        'Inactive User Deletion', 
        'Inactive User Deletion', 
        'manage_options', 
        'inactive-user-deletion', 
        'iaud_inactive_user_deletion_page', 
        'dashicons-trash'
    );
}

// Display settings page and save options
function iaud_inactive_user_deletion_page() {
    // Ensure wp_unslash is used on nonce before verification
    if (isset($_POST['iaud_form_nonce'])) {
        // Sanitize the nonce
        $nonce = sanitize_text_field(wp_unslash($_POST['iaud_form_nonce'])); 
        // Verify the nonce
        if (wp_verify_nonce($nonce, 'iaud_form_action')) {
            // Process form data and update options if nonce is verified
            $updated = false;
            if (isset($_POST['iaud_days_active'])) {
                $iaud_days_active = absint($_POST['iaud_days_active']);
                update_option('iaud_days_active', $iaud_days_active);
                $updated = true;
            }
            if (isset($_POST['iaud_reassign_user'])) {
                $iaud_reassign_user = sanitize_text_field(wp_unslash($_POST['iaud_reassign_user']));
                update_option('iaud_reassign_user', $iaud_reassign_user);
                $updated = true;
            }
            if (isset($_POST['iaud_warning_days_first'])) {
                $iaud_warning_days_first = absint($_POST['iaud_warning_days_first']);
                update_option('iaud_warning_days_first', $iaud_warning_days_first);
                $updated = true;
            }
            if (isset($_POST['iaud_warning_days_final'])) {
                $iaud_warning_days_final = absint($_POST['iaud_warning_days_final']);
                update_option('iaud_warning_days_final', $iaud_warning_days_final);
                $updated = true;
            }
            if (isset($_POST['iaud_email_subject_first'])) {
                $iaud_email_subject_first = sanitize_text_field(wp_unslash($_POST['iaud_email_subject_first']));
                update_option('iaud_email_subject_first', $iaud_email_subject_first);
                $updated = true;
            }
            if (isset($_POST['iaud_email_message_first'])) {
                $iaud_email_message_first = sanitize_textarea_field(wp_unslash($_POST['iaud_email_message_first']));
                update_option('iaud_email_message_first', $iaud_email_message_first);
                $updated = true;
            }
            if (isset($_POST['iaud_email_subject_final'])) {
                $iaud_email_subject_final = sanitize_text_field(wp_unslash($_POST['iaud_email_subject_final']));
                update_option('iaud_email_subject_final', $iaud_email_subject_final);
                $updated = true;
            }
            if (isset($_POST['iaud_email_message_final'])) {
                $iaud_email_message_final = sanitize_textarea_field(wp_unslash($_POST['iaud_email_message_final']));
                update_option('iaud_email_message_final', $iaud_email_message_final);
                $updated = true;
            }
            if (isset($_POST['iaud_disable_emails']) && $_POST['iaud_disable_emails'] == '1') {
                update_option('iaud_disable_emails', true);
                $updated = true;
            } else {
                update_option('iaud_disable_emails', false);
                $updated = true;
            }
            // Show success notice if any setting was updated
            if ($updated) {
                echo '<div class="updated"><p><strong>Settings saved successfully!</strong></p></div>';
            }
        } else {
            // If nonce verification fails, display an error message
            echo '<div class="error"><p><strong>Nonce verification failed. Please try again.</strong></p></div>';
        }
    }

        $iaud_days_active = get_option('iaud_days_active', 45); // Default to 45 days if not set
        $iaud_reassign_user = get_option('iaud_reassign_user', 1); // Default to Admin ID 1
        $iaud_warning_days_first = get_option('iaud_warning_days_first', 3); // Default to 3 days before first warning if not set
        $iaud_warning_days_final = get_option('iaud_warning_days_final', 1); // Default to 1 day before final warning if not set
        $iaud_email_subject_first = get_option('iaud_email_subject_first', 'Warning: Your account will be deleted in 3 days due to inactivity');
        $iaud_email_message_first = get_option('iaud_email_message_first', "Dear {user_name},\n\nYour account on {site_name} ({site_url}) has been inactive for a while. If you do not log in within the next 3 days, your account will be deleted.\n\nBest regards,\nThe {site_name} Team");
        $iaud_email_subject_final = get_option('iaud_email_subject_final', 'Final Warning: Your account will be deleted in 1 day due to inactivity');
        $iaud_email_message_final = get_option('iaud_email_message_final', "Dear {user_name},\n\nThis is a final reminder that your account on {site_name} ({site_url}) will be deleted in 1 day due to inactivity. Please log in as soon as possible to prevent deletion.\n\nBest regards,\nThe {site_name} Team");
        $iaud_disable_emails = get_option('iaud_disable_emails', false);

        $allUsers = get_users(['role__not_in' => ['Administrator']]);
    ?>
    <div class="wrap">
        <h2>Inactive User Deletion Settings</h2>
        <p><strong>Note:</strong> The following settings come with default values predefined in the code. You can override these values by modifying the settings below:</p>
      
        <form method="POST">
            <?php wp_nonce_field('iaud_form_action', 'iaud_form_nonce'); ?>  <!-- Nonce Field -->
            <table class="form-table">
                <tbody>
                    <!-- Delete After Inactive Days and Disable Emails Section -->
                        <tr>
                            <th><label for="iaud_days_active">Delete After Inactive Days</label></th>
                            <td>
                                <input name="iaud_days_active" id="iaud_days_active" type="number" value="<?php echo esc_attr($iaud_days_active); ?>" class="regular-text" min="1" step="1" />
                                <p class="description">Delete user after how many days of inactivity (Default: 45 days).</p>
                            </td>
                        </tr>

                    <!-- Reassign Posts Section -->
                        <tr>
                            <th><label for="iaud_reassign_user">Reassign Posts To</label></th>
                            <td>
                                <select name="iaud_reassign_user" id="iaud_reassign_user">
                                    <option value="" <?php selected($iaud_reassign_user, ''); ?>>Select User</option>
                                    <?php foreach ($allUsers as $user): ?>
                                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($iaud_reassign_user, $user->ID); ?>>
                                            <?php echo esc_html($user->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Select the user to reassign deleted users' posts (Default: Admin).</p>
                            </td>
                        </tr>
                    
                    <!-- First Warning Section -->
                        <tr>
                            <th colspan="2"><h3>First Warning</h3><br>
                                <p class="description">Configure first warning email timings and personalize the email template.</p>
                            </th>
                        </tr>
                        <tr>
                            <th><label for="iaud_warning_days_first">Days Before First Warning Email</label></th>
                            <td><input name="iaud_warning_days_first" id="iaud_warning_days_first" type="number" value="<?php echo esc_attr($iaud_warning_days_first); ?>" class="regular-text" min="1" step="1" /></td>
                        </tr>
                        <tr>
                            <th><label for="iaud_email_subject_first">Subject for First Warning Email</label></th>
                            <td><input name="iaud_email_subject_first" id="iaud_email_subject_first" type="text" value="<?php echo esc_attr($iaud_email_subject_first); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><label for="iaud_email_message_first">Message for First Warning Email</label></th>
                            <td><textarea name="iaud_email_message_first" id="iaud_email_message_first" class="large-text"><?php echo esc_textarea($iaud_email_message_first); ?></textarea></td>
                        </tr>

                    <!-- Final Warning Section -->
                        <tr>
                            <th colspan="2"><h3>Final Warning</h3><br>
                                <p class="description">Configure Final warning email timings and personalize the email template.</p>
                            </th>
                        </tr>
                        <tr>
                            <th><label for="iaud_warning_days_final">Days Before Final Warning Email</label></th>
                            <td><input name="iaud_warning_days_final" id="iaud_warning_days_final" type="number" value="<?php echo esc_attr($iaud_warning_days_final); ?>" class="regular-text" min="1" step="1" /></td>
                        </tr>
                        <tr>
                            <th><label for="iaud_email_subject_final">Subject for Final Warning Email</label></th>
                            <td><input name="iaud_email_subject_final" id="iaud_email_subject_final" type="text" value="<?php echo esc_attr($iaud_email_subject_final); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><label for="iaud_email_message_final">Message for Final Warning Email</label></th>
                            <td><textarea name="iaud_email_message_final" id="iaud_email_message_final" class="large-text"><?php echo esc_textarea($iaud_email_message_final); ?></textarea></td>
                        </tr>
                    
                    <!-- Disable Warning Emails Section -->
                        <tr>
                            <th><label for="iaud_disable_emails">Disable Warning Emails Before Deletion</label></th>
                            <td>
                                <input name="iaud_disable_emails" id="iaud_disable_emails" type="checkbox" value="1" <?php checked($iaud_disable_emails, true); ?> />
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


// Add "Last Login" & "Custom Posts Count" column to users list
add_filter('manage_users_columns', 'iaud_add_last_login_column');
function iaud_add_last_login_column($columns) {
    $columns['last_login'] = 'Last Login';
    $columns['post_count'] = 'Custom Posts'; // Column header
    return $columns;
}

// Populate the column with the "Last Login" & "Custom Posts Count" for each user
add_action('manage_users_custom_column', 'iaud_post_count_column_content', 10, 3);
function iaud_post_count_column_content($value, $column_name, $user_id) {
    // "Last Login"
    if ($column_name == 'last_login') {
        $last_login = get_user_meta($user_id, 'last_login', true);
        $value = $last_login ? '<div title="Last login: ' . gmdate('F j, Y, g:i a', $last_login) . '">' . human_time_diff($last_login) . '</div>' : 'No record';
    }
    // "Custom Posts Count"
    if ($column_name == 'post_count') {
        // Get all custom post types dynamically
        $post_types = iaud_all_post_types_with_names(); 
        // Add 'post' and 'page' to the list if needed
        $post_types[] = 'post';
        $post_types[] = 'page';
        // Get the count of all posts (standard + custom post types) for the user
        $args = array(
            'post_type' => $post_types, // Use the dynamically generated post types
            'author' => $user_id, // Get posts by the user
            'posts_per_page' => -1, // Get all posts
            'fields' => 'ids' // Only return the post IDs to optimize the query
        );
        $posts = get_posts($args);
        // Display the count of posts
        $value = count($posts);
    }
    return $value;
}