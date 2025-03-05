=== Inactive User Deletion ===

Contributors: shivangijavia2106
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Automatically deletes users inactive for a set number of days and assigns their posts to a selected user or admin before deletion.

== Description ==
The Inactive User Deletion plugin helps you manage user accounts that have been inactive for a set number of days by automatically deleting them. The plugin allows you to configure the number of days a user must remain inactive before deletion. Additionally, you can send warning emails to users before their account is deleted. These emails are customizable or can be disabled entirely. Before deleting the user, all their posts will be reassigned to a selected user or admin to prevent content loss.


== Features ==

- Automatically deletes users who have been inactive for a specified number of days.
- Sends warning emails to users before deletion (customizable).
- Option to disable warning emails.
- Option to modify the number of inactive days, and the timing for sending warning emails.
- Option to personalize the subject and content of the warning emails.
- Adds a "Last Login" column in the Users list in the admin panel.
- Assigns all posts of the inactive user to a selected user or admin before deleting the user.


== Installation ==
1. Download the plugin ZIP file.
2. In your WordPress admin dashboard, go to **Plugins** > **Add New**.
3. Click **Upload Plugin** and choose the ZIP file you downloaded.
4. Click **Install Now** and then **Activate** the plugin.

Alternatively, you can manually upload the plugin to the `/wp-content/plugins/` directory.

== Configuration ==

Once the plugin is activated, you can configure the settings by following these steps:

1. Go to **Dashboard** > **Inactive User Deletion** in the WordPress admin panel.
2. Configure the following settings:

   - **Delete After Inactive Days**:
     - Set the number of days of inactivity before the user is deleted.
     - **Default value**: 45 days.

   - **Assign Posts Before Deletion**:
     - Select a user (admin or another user) to whom the posts of the inactive user will be assigned before deletion.
     - **Default value**: Select the "admin" or any other user as needed.

   - **First Warning**:
     - **Days Before First Warning Email**: Set how many days before deletion the first warning email should be sent.
     - **Default value**: 3 days.
     - **Subject**: Customize the subject line for the first warning email.
     - **Default value**: "Warning: Your account will be deleted in 3 days due to inactivity".
     - **Message**: Customize the message body for the first warning email.
     - **Default value**: 
       ```
       Dear {user_name},

       Your account on {site_name} ({site_url}) has been inactive for a while. If you do not log in within the next 3 days, your account will be deleted.

       Best regards,
       The {site_name} Team
       ```

   - **Final Warning**:
     - **Days Before Final Warning Email**: Set how many days before deletion the final warning email should be sent.
     - **Default value**: 1 day.
     - **Subject**: Customize the subject line for the final warning email.
     - **Default value**: "Final Warning: Your account will be deleted in 1 day due to inactivity".
     - **Message**: Customize the message body for the final warning email.
     - **Default value**:
       ```
       Dear {user_name},

       This is a final reminder that your account on {site_name} ({site_url}) will be deleted in 1 day due to inactivity. Please log in as soon as possible to prevent deletion.

       Best regards,
       The {site_name} Team
       ```

   - **Disable Warning Emails Before Deletion**:
     - Check this box if you do not want to send warning emails before user deletion.
     - **Default value**: Unchecked (warning emails enabled).

3. Click **Save** to apply the changes.

== Cron Job ==

This plugin schedules a cron job to check for inactive users and delete them automatically. The task runs daily to check if there are any users who have been inactive for the specified number of days.

- The cron job can be modified or disabled if needed.

== Customization ==

- The email subject and content can be customized by editing the options in the settings page.
- You can also change the number of days for inactivity and the warning periods for the emails.
- Post reassignment can be easily modified by selecting a new user or admin under the settings.

== Support ==

If you encounter any issues or need assistance, please feel free to contact the plugin author via the [WordPress profile](https://profiles.wordpress.org/shivangijavia2106/).

== Frequently Asked Questions ==
Q: How can I change the number of inactive days before a user is deleted?
You can easily modify the number of inactive days under the Settings → Inactive User Deletion page in your WordPress admin.

Q: How can I customize the content of the warning emails?
The warning emails are sent based on the number of days before deletion as set in the plugin’s settings. You can edit the email content directly in the plugin\'s code. Modify the email subject and message in the function send_iud_warning_email().

Q: Will the plugin send me a notification when users are deleted?
Currently, the plugin only sends warning emails to the affected users. There is no built-in admin notification for deletions, but this can be added by modifying the plugin.

== Changelog ==
Initial release of the plugin with features to set inactive days, send warning emails, and automatically delete users.

== Upgrade Notice ==
This plugin was developed by Shivangee Javia. If you have any questions or need support, feel free to contact me via my WordPress profile.
