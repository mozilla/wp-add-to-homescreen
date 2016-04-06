<?php

include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-options.php');
include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-stats.php');

class WP_Add_To_Homescreen_Plugin {
    const STATS_ACTION = 'stats';

    private static $instance;

    public static function init() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private $options;

    private $stats;

    private $add2home_script;

    private $add2home_start_script;

    private $add2home_style;

    private $isMobile_script;

    private $localForage_script;

    private function __construct() {
        $plugin_main_file = plugin_dir_path(__FILE__) . 'wp-add-to-homescreen.php';
        $this->stats = WP_Add_To_Homescreen_Stats::get_stats();
        $this->options = WP_Add_To_Homescreen_Options::get_options();
        $this->set_urls();
        $this->generate_sw();
        add_action('wp_ajax_nopriv_' . self::STATS_ACTION, array($this, 'register_statistics'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_head', array($this, 'add_theme_and_icons'));
        register_activation_hook($plugin_main_file, array($this, 'activate'));
        register_deactivation_hook($plugin_main_file, array($this, 'deactivate'));

        Mozilla\WP_Serve_File::getInstance()->add_file('add2home.svg', array($this, 'generate_add2home_icon'));
    }

    private function set_urls() {
        $this->localForage_script = plugins_url(
            '/lib/vendor/localforage/dist/localforage.nopromises.min.js',
            __FILE__
        );
        $this->isMobile_script = plugins_url('/lib/vendor/isMobile/isMobile.min.js', __FILE__);
        $this->add2home_script = plugins_url('/lib/js/add-to-homescreen.js', __FILE__);
        $this->add2home_start_script = plugins_url(
            '/lib/js/start-add-to-homescreen.js',
            __FILE__
        );
        $this->add2home_style = plugins_url('/lib/css/style.css', __FILE__);
    }

    private function generate_sw() {
        // An empty SW only to meet Chrome add to homescreen banner requirements.
        Mozilla\WP_SW_Manager::get_manager()->sw()->add_content(function () { });
    }

    public function enqueue_assets() {
        wp_enqueue_style('add-to-homescreen-style', $this->add2home_style);

        wp_enqueue_script('isMobile-script', $this->isMobile_script);
        wp_enqueue_script('localforage-script', $this->localForage_script);
        wp_register_script(
            'add-to-homescreen',
            $this->add2home_script,
            array('isMobile-script', 'localforage-script'),
            false,
            true
        );
        $app_name = $this->options->get('app-name');
        wp_localize_script('add-to-homescreen', 'wpAddToHomescreenSetup', array(
            'libUrl' => plugins_url('lib/', __FILE__),
            'title' => sprintf(__('Add %s to home screen', 'add-to-homescreen'), $app_name['value']),
            'dismissText' => __('Got it!', 'add-to-homescreen'),
            'statsEndPoint' => admin_url('/admin-ajax.php?action=' . self::STATS_ACTION),
            'add2homeIconUrl' => Mozilla\WP_Serve_File::getInstance()->get_relative_to_host_root_url('add2home.svg')
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
        $icon = $this->options->get('icon');
        echo '<meta name="theme-color" content="' . $this->options->get('theme-color') . '" />';
        echo '<link rel="icon" sizes="144x144" href="' . $icon['url'] . '" />';
    }

    public function register_statistics() {
        $this->stats->process_event($_POST['event'], $_POST);
    }

    public function activate() {
        $this->generate_manifest();
        Mozilla\WP_Serve_File::getInstance()->invalidate_files(array('add2home.svg'));
    }

    public function deactivate() {
        $this->remove_manifest();
    }

    private function generate_manifest() {
        $icon = $this->options->get('icon');
        $app_name = $this->options->get('app-name');

        $manifest = Mozilla\WebAppManifestGenerator::getInstance();
        $manifest->set_field('name', get_bloginfo('name'));
        $manifest->set_field('short_name', $app_name['value']);
        $manifest->set_field('display', 'standalone');
        $manifest->set_field('orientation', 'portrait');
        $manifest->set_field('start_url', home_url('/', 'relative'));
        $manifest->set_field('icons', array(
            array(
                'src' => $icon['url'],
                'sizes' => '144x144',
                'type' => $icon['mime']
            )
        ));
    }

    private function remove_manifest() {
        $manifest = Mozilla\WebAppManifestGenerator::getInstance();
        $fields = array('name', 'short_name', 'display', 'orientation', 'start_url', 'icons');
        foreach ($fields as $field) {
            $manifest->set_field($field, NULL);
        }
    }

    public function generate_add2home_icon() {
        $theme_color = $this->options->get('theme-color');
        $svg = file_get_contents(plugin_dir_path(__FILE__) . 'lib/imgs/add2home.svg');
        return array(
            'content' => str_replace('theme-color', $theme_color, $svg),
            'contentType' => 'img/svg+xml'
        );
    }

}

?>
