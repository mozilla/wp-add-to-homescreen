<?php
include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-options.php');

class WP_Add_To_Homescreen_Stats {

    private static $types_for_events = array(
        'prompted' => 'counter',
        'installed' => 'counter',
        'instructions-shown' => 'counter'
    );

    private static $instance;

    public static function get_stats() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_option_name($event) {
        return 'event:' . $event;
    }

    private $options;

    private function __construct() {
        $this->options = WP_Add_To_Homescreen_Options::get_options();
    }

    public function get($event) {
        return $this->options->get(self::get_option_name($event));
    }

    public function process_event($event, $data) {
        if (!$event || !array_key_exists($event, self::$types_for_events)) {
            return;
        }

        $type = self::$types_for_events[$event];
        $handler = "process_$type";
        $this->$handler($event, $data);
        wp_die();
    }

    private function process_counter($event, $data) {
        $option_name = self::get_option_name($event);
        $current = $this->options->get($option_name) || 0;
        $this->options->set($option_name, $current + 1);
    }
}

?>