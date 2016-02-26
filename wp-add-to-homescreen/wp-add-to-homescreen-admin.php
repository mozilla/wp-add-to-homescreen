<?php

load_plugin_textdomain('wpappmanifest', false, dirname(plugin_basename(__FILE__)) . '/lang');

class WebAppManifest_Admin {
  private static $instance;

  public function __construct() {
    add_action('admin_menu', array($this, 'on_admin_menu'));
  }

  public static function init() {
    if (!self::$instance) {
      self::$instance = new self();
    }
  }

  public function on_admin_menu() {
    add_options_page(__('Web App Manifest Options', 'wpappmanifest'), __('Web App Manifest', 'wpappmanifest'), 'manage_options', 'web-app-manifest-options', array($this, 'options'));
  }

  public function options() {
  }
}
?>
