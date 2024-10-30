=== Code Monitor ===
Contributors: Sam Samie
Website: https://wpcodemonitor.com
Donate link: https://github.com/SS-4
Tags: php, files, security, monitor, email, notifications, subdirectories, file types, htaccess
Requires at least: 4.0
Tested up to: 6.3.2
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Code Monitor now includes advanced monitoring for specific file types, subdirectory scanning, and .htaccess file change detection, with email alerts.

== Description ==

Code Monitor is an enhanced, user-friendly plugin that scrutinizes your WordPress directories for new files based on specified types (e.g., PHP, HTML, JS) and changes in the .htaccess file. It includes subdirectory monitoring and alerts you via email when new files are detected or if there are modifications to the .htaccess file.

This tool is essential for website administrators aiming to stay vigilant against potential security threats. Unauthorized file creation or changes to critical files like .htaccess in WordPress directories can indicate a security compromise. With customizable monitoring settings, Code Monitor offers comprehensive protection for your website.

== Screenshots ==

1. Main settings page of Code Monitor where you can configure directory paths, file types to monitor, .htaccess monitoring, and email settings.
2. Email alert showing details of newly detected files or .htaccess modifications.

== Installation ==

= From your WordPress dashboard =
1. Visit 'Plugins > Add New'
2. Search for 'Code Monitor'
3. Activate Code Monitor from your Plugins page.

= From WordPress.org =
1. Search, select, and download 'Code Monitor'
2. Upload the '.zip' file to the '/wp-content/plugins/code-monitor/' directory
3. Activate Code Monitor from your Plugins page.

== FAQ ==

= What file types can this plugin monitor? =
Starting from version 1.2, you can specify any file types to monitor (e.g., php, html, js). Enter the file extensions in the settings to set up monitoring.

= Does the plugin support subdirectory monitoring? =
Yes. As of version 1.2, Code Monitor can scan both primary directories and their subdirectories.

= Can this plugin monitor changes to the .htaccess file? =
Yes, version 1.2 introduces the capability to monitor modifications to the .htaccess file in the specified directory.

= Will this plugin work if I don't type anything in the 'Directories to monitor'? =
By default, the plugin oversees all the directories visible to your web server under your WordPress folder, even if you leave that field empty.

= Is it possible to modify the recipient email address for notifications? =
Certainly. The plugin's settings page allows you to input your desired email address for receiving notifications.

== Changelog ==

= 1.2 =
* Added feature to monitor specific file types.
* Introduced monitoring for changes to the .htaccess file.
* Enhanced UI for easier configuration of new features.
* Improved error logging and performance optimizations.
* Default notification email set to the WordPress admin email upon plugin activation.

= 1.1 =
* Initial release with basic monitoring functionalities.

== Support ==

We hope you find Code Monitor helpful! For support, please use the [Support Forum](https://wordpress.org/support/plugin/code-monitor/).

== Upgrade Notice ==

= 1.2 =
Major update with new features for file type and .htaccess monitoring. Update recommended for enhanced security monitoring capabilities.
