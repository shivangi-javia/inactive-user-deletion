<?php

// Function to send inactivity warning email
add_filter('wp_mail_content_type', function() {
    return 'text/html'; // Set content type to HTML
});

function iaud_warning_email($user, $days_left) {
    $user_email = $user->user_email;
    $user_name = $user->display_name;

    $site_name = get_bloginfo('name'); // Get the site name
    $site_url = get_bloginfo('url'); // Get the site URL

    $subject = $days_left == 3 ? get_option('iaud_email_subject_first') : get_option('iaud_email_subject_final');
    $message = $days_left == 3 ? get_option('iaud_email_message_first') : get_option('iaud_email_message_final');

    // Replace placeholders in message
    $message = str_replace('{user_name}', $user_name, $message);
    $message = str_replace('{site_name}', $site_name, $message);
    $message = str_replace('{site_url}', $site_url, $message);

    // Add HTML structure to the email message
    $message = '<html><body>';
    $message .= '<p>' . nl2br($message) . '</p>'; // Ensure line breaks are respected
    $message .= '<p>Best regards,<br>' . $site_name . '</p>';
    $message .= '</body></html>';

    // Send the email
    wp_mail($user_email, $subject, $message);
}
