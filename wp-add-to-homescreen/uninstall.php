<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-options.php');

WP_Offline_Content_Options::get_options()->remove_all();
?>
