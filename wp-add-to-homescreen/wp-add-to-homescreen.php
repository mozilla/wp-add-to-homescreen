<?php
/*
Plugin Name: Add to Home Screen
Plugin URI: https://github.com/marco-c/wp-app-manifest
Description: Make your WordPress sites to appear in mobile home screens.
Version: 0.0.1
Author: Mozilla
Author URI: https://www.mozilla.org/
License: GPLv2 or later
Text Domain: wpaddtohomescreen
*/

load_plugin_textdomain('wpaddtohomescreen', false, dirname(plugin_basename(__FILE__)) . '/lang');

require_once(plugin_dir_path(__FILE__) . 'wp-add-to-homescreen-main.php');
require_once(plugin_dir_path(__FILE__) . 'wp-add-to-homescreen-db.php');

WebAppManifest_Main::init();

if (is_admin()) {
  require_once(plugin_dir_path(__FILE__) . 'wp-add-to-homescreen-admin.php');
  WebAppManifest_Admin::init();
}

register_activation_hook(__FILE__, array('WebAppManifest_DB', 'on_activate'));
register_deactivation_hook(__FILE__, array('WebAppManifest_DB', 'on_deactivate'));
register_uninstall_hook(__FILE__, array('WebAppManifest_DB', 'on_uninstall'));

?>
