<?php

// TODO: Load manifest plugin
include_once(plugin_dir_path(__FILE__) . 'vendor/marco-c/wp-web-app-manifest-generator/WebAppManifestGenerator.php');

class WP_Add_To_Homescreen_Plugin {
    private static $instance;

    public static function init() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private $options;

    private $add2home_script;

    private $add2home_start_script;

    private $add2home_style;

    private function __construct() {
        $plugin_main_file = plugin_dir_path(__FILE__) . 'wp-add-to-homescreen.php';
        $this->set_urls();
        $this->generate_manifest();
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        register_activation_hook($plugin_main_file, array($this, 'activate'));
        register_deactivation_hook($plugin_main_file, array($this, 'deactivate'));
    }

    private function set_urls() {
        $this->add2home_script = plugins_url('/lib/js/add-to-homescreen.js', __FILE__);
        $this->add2home_start_script = plugins_url(
            '/lib/js/start-add-to-homescreen.js',
            __FILE__
        );
        $this->add2home_style = plugins_url('/lib/css/style.css', __FILE__);
    }

    private function generate_manifest() {
        $manifest = WebAppManifestGenerator::getInstance();
        $manifest->set_field('name', get_bloginfo('name'));
        $manifest->set_field('display', 'standalone');
        $manifest->set_field('orientation', 'portrait');
        $manifest->set_field('start_url', home_url('/', 'relative'));
        $manifest->set_field('icons', array(
            array(
                'src' => plugins_url('/lib/imgs/rocket.png', __FILE__),
                'sizes' => '120x120',
                'type' => 'image/png'
            )
        ));
    }

    public function enqueue_assets() {
        wp_enqueue_style('add-to-homescreen-style', $this->add2home_style);

        wp_register_script('add-to-homescreen', $this->add2home_script, array(), false, true);
        wp_localize_script('add-to-homescreen', 'wpAddToHomescreenSetup', array(
            'libUrl' => plugins_url('lib/', __FILE__),
            'invitationText' => 'Make this site appear among your apps!',
            'dismissText' => 'Got it!'
        ));
        wp_enqueue_script('add-to-homescreen');
        wp_enqueue_script(
            'start-add-to-homescreen',
            $this->add2home_start_script,
            array('add-to-homescreen'),
            false,
            true
        );
    }

    public function activate() {
    }

    public static function deactivate() {
    }
}

?>