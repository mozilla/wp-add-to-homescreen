<?php

// TODO: Load manifest plugin
include_once(plugin_dir_path(__FILE__) . 'vendor/marco-c/wp-web-app-manifest-generator/WebAppManifestGenerator.php');
include_once(plugin_dir_path(__FILE__) . 'vendor/mozilla/wp-sw-manager/class-wp-sw-manager.php');

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

    private $isMobile_script;

    private function __construct() {
        $plugin_main_file = plugin_dir_path(__FILE__) . 'wp-add-to-homescreen.php';
        $this->set_urls();
        $this->generate_manifest();
        $this->generate_sw();
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_head', array($this, 'add_theme_and_icons'));
        register_activation_hook($plugin_main_file, array($this, 'activate'));
        register_deactivation_hook($plugin_main_file, array($this, 'deactivate'));
    }

    private function set_urls() {
        $this->isMobile_script = plugins_url('/lib/vendor/isMobile/isMobile.min.js', __FILE__);
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
                'sizes' => '144x144',
                'type' => 'image/png'
            )
        ));
    }

    private function generate_sw() {
        // An empty SW only to meet Chrome add to homescreen banner requirements.
        WP_SW_Manager::get_manager()->sw()->add_content(function () { });
    }

    public function enqueue_assets() {
        wp_enqueue_style('add-to-homescreen-style', $this->add2home_style);

        wp_enqueue_script('isMobile', $this->isMobile_script);
        wp_register_script('add-to-homescreen', $this->add2home_script, array('isMobile'), false, true);
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

    public function add_theme_and_icons() {
        $icon_path = plugins_url('/lib/imgs/rocket.png', __FILE__);
        echo '<meta name="theme-color" content="#d53c30" />';
        echo '<link rel="icon" sizes="144x144" href="' . $icon_path . '" />';
    }

    public function activate() {
    }

    public static function deactivate() {
    }
}

?>