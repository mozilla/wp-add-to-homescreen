<?php

class WP_Add_To_Homescreen_Options {
    const OPTIONS_PREFIX = 'addtohomescreen_';

    private static $instance;

    private static $DEFAULTS = array(
    );

    public static function get_options() {
        if(!self::$instance) {
            self::$DEFAULTS['addtohomescreen_icon'] = plugins_url('/lib/imgs/rocket.png', __FILE__);
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
    }

    public function n($option) {
        return self::OPTIONS_PREFIX . $option;
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
        $option = $name;
        $value = get_option($option);
        return ($value !== false) ? $value : self::$DEFAULTS[$name];
    }
}

?>