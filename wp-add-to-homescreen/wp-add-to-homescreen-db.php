<?php

class WebAppManifest_DB {
  private static $instance;
  private $version = '0.0.1';

  public function __construct() {
  }

  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public static function on_activate() {
    global $wpdb;

    if (WebAppManifest_DB::getInstance()->version === get_option('webappmanifest_db_version')) {
      return;
    }

    update_option('webappmanifest_db_version', WebAppManifest_DB::getInstance()->version);
  }

  public static function on_deactivate() {
  }

  public static function on_uninstall() {
    delete_option('webappmanifest_db_version');
  }
}

?>
