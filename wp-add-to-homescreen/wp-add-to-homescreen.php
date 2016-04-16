<?php
/*
Plugin Name: Add to Home Screen
Description: Make your WordPress sites to appear in mobile home screens.
Plugin URI: https://github.com/mozilla/add-to-homescreen
Version: 0.3.1
Author: Mozilla
Author URI: https://www.mozilla.org/
License: GPLv2 or later
Text Domain: add-to-homescreen
*/

require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

load_plugin_textdomain('add-to-homescreen', false, dirname(plugin_basename(__FILE__)) . '/lang');

include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-plugin.php');

if (is_admin()) {
    include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-admin.php');
    WP_Add_To_Homescreen_Admin::init();
}
WP_Add_To_Homescreen_Plugin::init();
?>
