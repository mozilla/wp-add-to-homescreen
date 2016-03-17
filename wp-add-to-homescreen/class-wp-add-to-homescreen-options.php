<?php

class WP_Add_To_Homescreen_Options {
    private static $instance;

    private static $DEFAULTS = array(
    );

    public static function get_options() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
    }

    public function set_defaults() {
        foreach (self::$DEFAULTS as $name => $value) {
            if (!get_option($name)) {
                add_option($name, $value);
            }
        }
    }

    public function remove_all() {
        foreach (self::$DEFAULTS as $name => $value) {
            delete_option($name);
        }
    }

    public function set($name, $value) {
        update_option($name, $value);
        return $this;
    }

    public function get($name) {
        return get_option($name);
    }
}

?>