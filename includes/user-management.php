<?php

// Capture user's last login time
add_action('wp_login', 'iaud_capture_user_login_time', 10, 2);
function iaud_capture_user_login_time($user_login, $user) {
    update_user_meta($user->ID, 'last_login', time());
}

// Function to delete inactive users
function iaud_delete_inactive_users() {
    $iaud_days_active = get_option('iaud_days_active', 45); // Default to 45 days
    if (empty($iaud_days_active)) {
        return; // No days set, no deletion needed
    }
    $iaud_warning_days_first = get_option('iaud_warning_days_first', 3); // Default to 3 days for first warning
    $iaud_warning_days_final = get_option('iaud_warning_days_final', 1); // Default to 1 day for final warning
    $iaud_disable_emails = get_option('iaud_disable_emails', false); // Disable emails if option is true
    // Get the selected user to reassign posts to
    $iaud_reassign_user = get_option('iaud_reassign_user', 1); // Default to Admin (ID 1) if no user selected
    $users = get_users(['role__not_in' => ['Administrator']]); // Exclude Administrators
    foreach ($users as $user) {
        $last_login = get_user_meta($user->ID, 'last_login', true);
        if (!empty($last_login)) {
            $last_active = gmdate('F j, Y, g:i a', $last_login);
            // $today = gmdate('F j, Y, g:i a');
            $today = current_time('timestamp'); // Use current_time('timestamp') instead of gmdate()
            // Create DateTime objects for the last login and today
            $start = new DateTime();
            $start->setTimestamp($last_login); // Set last login timestamp

            $end = new DateTime();
            $end->setTimestamp($today); // Set today's timestamp

            $difference = $start->diff($end);
            $iaud_days_inactive = $difference->days;

            // Delete user if inactive for the specified days and assign data to another user
            $current_time = time();
            if ($iaud_days_inactive >= $iaud_days_active) {
                // Check if the user is 3 or 1 day(s) before deletion
                if (!$iaud_disable_emails) {
                    // Check if the user has already received a warning before sending a new one
                    if ($iaud_days_inactive == $iaud_days_active - $iaud_warning_days_first && !get_user_meta($user->ID, 'iaud_first_warning_sent', true)) {
                        iaud_warning_email($user, $iaud_warning_days_first);
                        update_user_meta($user->ID, 'iaud_first_warning_sent', true);
                    } elseif ($iaud_days_inactive == $iaud_days_active - $iaud_warning_days_final && !get_user_meta($user->ID, 'iaud_final_warning_sent', true)) {
                        iaud_warning_email($user, $iaud_warning_days_final);
                        update_user_meta($user->ID, 'iaud_final_warning_sent', true);
                    }

                }
                // Reassign the posts, comments, etc. to admin before deleting the user
                iaud_reassign_posts_to_admin($user->ID, $iaud_reassign_user);
                // Delete the user after reassignment
                wp_delete_user($user->ID);
            }
        }
    }
}

// Function to reassign posts, pages, and comments to the admin
function iaud_reassign_posts_to_admin($user_id, $reassign_user_id) {
    // Get all custom post types dynamically
    $post_types = iaud_all_post_types_with_names(); 
    // Add 'post' and 'page' to the list if needed
    $post_types[] = 'post';
    $post_types[] = 'page';
    // Reassign posts to selected User
    $args = [
        'post_type' => $post_types, // Use the dynamically generated post types
        'author' => $user_id,
        'posts_per_page' => -1
    ];
    $user_posts = get_posts($args);
    // Skip if no posts to reassign
    if (empty($user_posts)) {
        return;
    }
    foreach ($user_posts as $post) {
        // Reassign each post to the admin user
        $post->post_author = $reassign_user_id;
        $post->post_status = 'publish';
        wp_update_post($post);
    }

    // Optionally reassign custom data or comments if necessary
    // Example for comments:
    $user_comments = get_comments(['user_id' => $user_id]);
    // Skip if no comments to reassign
    if (empty($user_comments)) {
        return;
    }
    foreach ($user_comments as $comment) {
        // Reassign comment to admin
        wp_update_comment([
            'comment_ID' => $comment->comment_ID,
            'user_id'    => $reassign_user_id
        ]);
    }
}