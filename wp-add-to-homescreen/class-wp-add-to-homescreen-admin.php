<?php

include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-stats.php');
include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-options.php');
include_once(plugin_dir_path(__FILE__) . 'vendor/marco-c/wp-web-app-manifest-generator/WebAppManifestGenerator.php');

// Based on: https://codex.wordpress.org/Creating_Options_Pages#Example_.232
class WP_Add_To_Homescreen_Admin {
    private static $instance;

    public static $options_page_id = 'add-to-homescreen-options';

    public static $options_group = 'add-to-homescreen-settings-group';

    public static function init() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private $options;

    private function __construct() {
        $this->options = WP_Add_To_Homescreen_Options::get_options();
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function admin_init() {
        $options = $this->options;
        $group = self::$options_group;
        register_setting($group, $options->o('icon'), array($this, 'sanitize_icon'));

        add_settings_section(
            'default',
            __('UI Configuration', 'add-to-homescreen'),
            function () {},
            self::$options_page_id
        );

        add_settings_field(
            $options->o('icon'),
            __('Home Screen icon', 'offline-content'),
            array($this, 'icon_input'),
            self::$options_page_id,
            'default'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_script('options-page-script', plugins_url('lib/js/options-page.js', __FILE__));
    }

    public function admin_menu() {
        add_options_page(
            __('Add to Home Screen', 'add-to-homescreen'), __('Add to Home Screen', 'add-to-homescreen'),
            'manage_options', self::$options_page_id, array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        include_once(plugin_dir_path(__FILE__) . 'lib/pages/admin.php');
    }

    public function icon_input() {
        $id = $this->options->o('icon');
        $current_icon = $this->options->get('icon');
        $explanation = __('Icon to appear in the Home Screen (size must be 144x144px)', 'add-to-homescreen');
        ?>
        <img id="icon-preview" style="width: 144px; height: 144px;"
         src="<?php echo $current_icon['url']; ?>"
         alt="<?php echo $explanation; ?>"
        />
        <p class"small-text"><?php echo $explanation; ?></p>
        <p>
         <input type="hidden" id="icon-mime" name="<?php echo "$id" . '[mime]'; ?>"
         value="<?php echo $current_icon['mime']; ?>"/>
         <input type="hidden" id="icon-url" name="<?php echo "$id" . '[url]'; ?>"
          value="<?php echo $current_icon['url']; ?>"/>
         <input type="button" class="button" id="select-icon-button"
          value="<?php _e('Select...', 'add-to-homescreen'); ?>" />
        </p>
        <?php
    }

    public function sanitize_icon($new_icon) {
        $current_icon = $this->options->get('icon');
        if (!isset($new_icon['url'])) {
            return $current_icon;
        }
        if ($current_icon !== $new_icon) {
            WebAppManifestGenerator::getInstance()->set_field('icons', array(
                array(
                    'src' => $new_icon['url'],
                    'sizes' => '144x144',
                    'type' => $new_icon['mime']
                )
            ));
        }
        return $new_icon;
    }

    public function add_dashboard_widgets() {
        wp_add_dashboard_widget('wp-add-to-homescreen_widget', __('Add To Home Screen', 'add-to-homescreen'), array($this, 'print_widget'));
    }

    public function print_widget() {
        $stats = WP_Add_To_Homescreen_Stats::get_stats();
        $instructions_shown = $stats->get('instructions-shown');
        $prompted = $stats->get('prompted');
        $installed = $stats->get('installed');

        echo '<p>';
        printf(_n(
            '<strong>%s</strong> user saw <em>how to add to home screen</em> instructions.',
            '<strong>%s</strong> users saw <em>how to add to home screen</em> instructions.',
            $instructions_shown, 'add-to-homescreen'
        ), number_format_i18n($instructions_shown));
        echo '</p>';

        echo '<p>';
        printf(_n(
            '<strong>%s</strong> user has been prompted about adding your site as an application.',
            '<strong>%s</strong> users have been prompted about adding your site as an application.',
            $prompted, 'add-to-homescreen'
        ), number_format_i18n($prompted));
        echo '</p>';

        echo '<p>';
        printf(_n(
            '<strong>%s</strong> user has added your site as an application.',
            '<strong>%s</strong> users have added your site as an application.',
            $installed, 'add-to-homescreen'
        ), number_format_i18n($installed));
        echo '</p>';
    }

}

?>
