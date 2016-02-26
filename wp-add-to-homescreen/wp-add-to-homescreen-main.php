<?php

require_once(plugin_dir_path(__FILE__) . 'wp-app-manifest-db.php');
require_once(plugin_dir_path(__FILE__) . 'vendor/marco-c/wp-web-app-manifest-generator/WebAppManifestGenerator.php');

class WebAppManifest_Main {
  private static $instance;

  public function __construct() {
    $manifestGenerator = WebAppManifestGenerator::getInstance();
  }

  public static function init() {
    if (!self::$instance) {
      self::$instance = new self();
    }
  }
}

?>
