<?php
/**
 * Plugin Name: Code Monitor
 * Description: Monitors new files in specified types and .htaccess changes, dispatches email alerts. Includes subdirectory monitoring and file type filters.
 * Version:     1.2
 * Author:      Sam Samie
 * Author URI:  https://wpcodemonitor.com
 * Donate link: https://github.com/SS-4
 */

if (!defined('ABSPATH')) {
    exit;
}

function code_monitor_sanitize_directory($dir) {
    return preg_replace('/[^a-zA-Z0-9\/-_]/', '', $dir);
}

function code_monitor_sanitize_email($email) {
    return sanitize_email($email);
}

function code_monitor_scan_directory($dir, $recursive = false) {
    $files = array();
    if ($recursive) {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            $files[] = $file->getPathname();
        }
    } else {
        $files = array_diff(scandir($dir), array('..', '.'));
    }
    return $files;
}

function code_monitor_check_for_new_files() {
    $dir = get_option('code_monitor_directory');
    $email = get_option('code_monitor_email');
    $monitor_subdirs = get_option('code_monitor_subdirs', false);
    $file_types = get_option('code_monitor_file_types', 'php');
    $monitor_htaccess = get_option('code_monitor_htaccess', false);

    if (!file_exists($dir)) {
        return;
    }

    $file_types_array = explode(',', $file_types);
    $all_files = code_monitor_scan_directory($dir, $monitor_subdirs);
    $htaccess_path = $dir . '/.htaccess';

    try {
        $filtered_files = array();
        foreach ($all_files as $file) {
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), $file_types_array)) {
                $filtered_files[] = $file;
            }
            if ($monitor_htaccess && $file === $htaccess_path && filemtime($htaccess_path) > get_option('code_monitor_last_htaccess_mtime', 0)) {
                $filtered_files[] = $file;
                update_option('code_monitor_last_htaccess_mtime', filemtime($htaccess_path));
            }
        }
    } catch (Exception $e) {
        error_log('Error scanning directory in Code Monitor: ' . esc_html($e->getMessage()));
        return;
    }

    $old_files = get_option('code_monitor_files', array());
    update_option('code_monitor_files', $filtered_files);

    $new_files = array_diff($filtered_files, $old_files);

    if (!empty($new_files)) {
        wp_mail($email, esc_html__('New Files or .htaccess Changes Detected', 'code-monitor'), esc_html__("New files were added or .htaccess was modified in the directory:\n\n", 'code-monitor') . implode("\n", $new_files));
    }
}

if (! wp_next_scheduled('code_monitor_event')) {
    wp_schedule_event(time(), 'hourly', 'code_monitor_event');
}

add_action('code_monitor_event', 'code_monitor_check_for_new_files');

function code_monitor_activate() {
    if (!get_option('code_monitor_email')) {
        update_option('code_monitor_email', get_option('admin_email'));
    }
    if (!get_option('code_monitor_last_htaccess_mtime')) {
        update_option('code_monitor_last_htaccess_mtime', 0);
    }
}
register_activation_hook(__FILE__, 'code_monitor_activate');

function code_monitor_menu() {
    add_menu_page(
        esc_html__('Code Monitor Settings', 'code-monitor'),
        esc_html__('Code Monitor', 'code-monitor'),
        'manage_options',
        'code-monitor',
        'code_monitor_options',
        'dashicons-shield',
        5  // Changed position from 99 to 5
    );
    
}

add_action('admin_menu', 'code_monitor_menu');

function code_monitor_options() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'code-monitor'));
    }

    if (isset($_POST["code_monitor_directory"], $_POST["code_monitor_email"]) && check_admin_referer('code_monitor_save_settings', 'code_monitor_settings_nonce')) {
        update_option('code_monitor_directory', sanitize_text_field($_POST["code_monitor_directory"]));
        update_option('code_monitor_email', sanitize_email($_POST["code_monitor_email"]));
        update_option
        ('code_monitor_subdirs', isset($_POST["code_monitor_subdirs"]));
        update_option('code_monitor_file_types', sanitize_text_field($_POST["code_monitor_file_types"]));
        update_option('code_monitor_htaccess', isset($_POST["code_monitor_htaccess"]));

        add_settings_error(
            'code_monitor_messages',
            'code_monitor_message',
            __('Settings Saved', 'code-monitor'),
            'updated'
        );
    }

    settings_errors('code_monitor_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Code Monitor Settings', 'code-monitor'); ?></h1>
        <form method="post" action="">
            <?php
            wp_nonce_field('code_monitor_save_settings', 'code_monitor_settings_nonce');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="code_monitor_directory"><?php esc_html_e('Directory Path', 'code-monitor'); ?></label></th>
                    <td>
                        <input name="code_monitor_directory" type="text" id="code_monitor_directory" value="<?php echo esc_attr(get_option('code_monitor_directory')); ?>" class="regular-text ltr" />
                        <p class="description"><?php esc_html_e('Enter the path of the directory you want to monitor. Leave blank to monitor the WordPress root directory.', 'code-monitor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="code_monitor_email"><?php esc_html_e('Notification Email', 'code-monitor'); ?></label></th>
                    <td>
                        <input name="code_monitor_email" type="email" id="code_monitor_email" value="<?php echo esc_attr(get_option('code_monitor_email')); ?>" class="regular-text ltr" />
                        <p class="description"><?php esc_html_e('Enter the email address where you want to receive notifications. Defaults to the WordPress admin email.', 'code-monitor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Include Subdirectories', 'code-monitor'); ?></th>
                    <td>
                        <input name="code_monitor_subdirs" type="checkbox" id="code_monitor_subdirs" <?php checked(get_option('code_monitor_subdirs', false)); ?> />
                        <p class="description"><?php esc_html_e('Check this box to include all subdirectories of the specified directory for monitoring.', 'code-monitor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="code_monitor_file_types"><?php esc_html_e('File Types to Monitor', 'code-monitor'); ?></label></th>
                    <td>
                        <input name="code_monitor_file_types" type="text" id="code_monitor_file_types" value="<?php echo esc_attr(get_option('code_monitor_file_types', 'php')); ?>" class="regular-text ltr" />
                        <p class="description"><?php esc_html_e('Enter file extensions separated by commas. Example: php,html,js', 'code-monitor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Monitor .htaccess File', 'code-monitor'); ?></th>
                    <td>
                        <input name="code_monitor_htaccess" type="checkbox" id="code_monitor_htaccess" <?php checked(get_option('code_monitor_htaccess', false)); ?> />
                        <p class="description"><?php esc_html_e('Check this box to monitor changes to the .htaccess file in the specified directory.', 'code-monitor'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function code_monitor_settings() {
    register_setting('code-monitor-settings', 'code_monitor_directory', 'code_monitor_sanitize_directory');
    register_setting('code-monitor-settings', 'code_monitor_email', 'code_monitor_sanitize_email');
    register_setting('code-monitor-settings', 'code_monitor_subdirs');
    register_setting('code-monitor-settings', 'code_monitor_file_types');
    register_setting('code-monitor-settings', 'code_monitor_htaccess');
}

add_action('admin_init', 'code_monitor_settings');
?>
