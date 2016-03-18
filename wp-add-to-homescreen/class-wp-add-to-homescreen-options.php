<?php

class WP_Add_To_Homescreen_Options {
    const OPTIONS_PREFIX = 'addtohomescreen_';

    private static $instance;

    private static $DEFAULTS = array(
    );

    public static function get_options() {
        if(!self::$instance) {
            self::$DEFAULTS['icon'] = array(
                'url' => plugins_url('/lib/imgs/rocket.png', __FILE__),
                'mime' => 'image/png'
            );
            self::$DEFAULTS['theme-color'] = '#1A1A1A';
            self::$DEFAULTS['app-name'] = array('type' => 'title', 'value' => get_bloginfo('name'));
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

    public function get_default($name) {
        if (!array_key_exists($name, self::$DEFAULTS)) {
            return NULL;
        }
        return self::$DEFAULTS[$name];
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
        return ($value !== false) ? $value : $this->get_default($name);
    }
}

?>