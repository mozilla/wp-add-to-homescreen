<?php

class WP_Add_To_Homescreen_Options {
    const OPTIONS_PREFIX = 'addtohomescreen_';

    private static $instance;

    private static $DEFAULTS = array(
    );

    public static function get_options($defaults=array()) {
        if(!self::$instance) {
            foreach ($defaults as $name => $value) {
                self::$DEFAULTS[$name] = $value;
            }
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
    }

    public function o($name) {
        return self::OPTIONS_PREFIX . $name;
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
        $option = $this->o($name);
        update_option($option, $value);
        return $this;
    }

    public function get($name) {
        $option = $this->o($name);
        $value = get_option($option);
        return ($value !== false) ? $value : self::$DEFAULTS[$name];
    }
}

?>