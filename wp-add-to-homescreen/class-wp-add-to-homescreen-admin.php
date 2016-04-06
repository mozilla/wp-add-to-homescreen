<?php

include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-stats.php');
include_once(plugin_dir_path(__FILE__) . 'class-wp-add-to-homescreen-options.php');

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
        add_action('settings_page_' . self::$options_page_id, array($this, 'enqueue_scripts'));
        add_action('update_option_' . $this->options->o('theme-color'), array($this, 'regenerate_icon'));
    }

    public function admin_init() {
        $options = $this->options;
        $group = self::$options_group;
        register_setting($group, $options->o('icon'), array($this, 'sanitize_icon'));
        register_setting($group, $options->o('theme-color'), array($this, 'sanitize_color'));
        register_setting($group, $options->o('app-name'), array($this, 'sanitize_app_name'));

        add_settings_section(
            'default',
            __('UI Configuration', 'add-to-homescreen'),
            function () {},
            self::$options_page_id
        );

        add_settings_field(
            $options->o('app-name'),
            __('App name', 'add-to-homescreen'),
            array($this, 'app_name_input'),
            self::$options_page_id,
            'default'
        );

        add_settings_field(
            $options->o('icon'),
            __('Theme color', 'add-to-homescreen'),
            array($this, 'color_input'),
            self::$options_page_id,
            'default'
        );

        add_settings_field(
            $options->o('theme-color'),
            __('Home Screen icon', 'add-to-homescreen'),
            array($this, 'icon_input'),
            self::$options_page_id,
            'default'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script(
            'options-page-script',
            plugins_url('lib/js/options-page.js', __FILE__),
            array('wp-color-picker')
        );
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

    public function app_name_input() {
        $name = $this->options->o('app-name');
        $current_app_name = $this->options->get('app-name');
        $current_type = $current_app_name['type'];
        $current_value = $current_app_name['value'];
        ?>
        <p><label><input type="radio" name="<?php echo $name . '[type]'; ?>"
                   <?php echo ($current_type === 'title' ? 'checked' : ''); ?>
                   value="title"/><?php _e('Use the Site Title', 'add-to-homescreen'); ?></label></p>
        <p><label><input type="radio" name="<?php echo $name . '[type]'; ?>"
                   <?php echo ($current_type === 'custom' ? 'checked' : ''); ?>
                   value="custom"/><?php _e('Custom:', 'add-to-homescreen'); ?></label>
                   <input class="short-text" type="text" name="<?php echo $name . '[value]'; ?>"
                   value="<?php echo $current_value; ?>"/>
        </p>
        <p class="description"><?php _e('This name will appear in your home screen labeling the icon so if you choose a custom name, try to choose one fitting the field provided above.', 'add-to-homescreen'); ?></p>
        <?php
    }

    public function color_input() {
        $name = $this->options->o('theme-color');
        $current_color = $this->options->get('theme-color');
        ?>
        <p>
          <input type="text" class="color-picker" name="<?php echo $name ?>"
           value="<?php echo $current_color; ?>"/>
        </p>
        <p class="description"><?php _e('The color for the overlay showing the instructions. Those browsers supporting themes will tint their UI with this color.', 'add-to-homescreen'); ?></p>
        <?php
    }

    public function icon_input() {
        $name = $this->options->o('icon');
        $current_icon = $this->options->get('icon');
        $explanation = __('Icon to appear in the Home Screen (size must be 144x144px)', 'add-to-homescreen');
        ?>
        <img id="icon-preview" style="width: 144px; height: 144px;"
         src="<?php echo $current_icon['url']; ?>"
         alt="<?php echo $explanation; ?>"
        />
        <p class="description"><?php echo $explanation; ?></p>
        <p>
         <input type="hidden" id="icon-mime" name="<?php echo "$name" . '[mime]'; ?>"
         value="<?php echo $current_icon['mime']; ?>"/>
         <input type="hidden" id="icon-url" name="<?php echo "$name" . '[url]'; ?>"
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
            Mozilla\WebAppManifestGenerator::getInstance()->set_field('icons', array(
                array(
                    'src' => $new_icon['url'],
                    'sizes' => '144x144',
                    'type' => $new_icon['mime']
                )
            ));
        }
        return $new_icon;
    }

    public function sanitize_color($new_color) {
        $current_color = $this->options->get('theme-color');
        if (empty($new_color) || !preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', $new_color)) {
            return $current_color;
        }
        return $new_color;
    }

    public function sanitize_app_name($new_app_name) {
        $current_app_name = $this->options->get('app-name');
        if (!$this->check_app_name_integrity($new_app_name)) {
            $this->add_illegal_app_name_error();
            return $current_app_name;
        }

        if ('title' === $new_app_name['type']) {
            $new_app_name['value'] = get_bloginfo('name');
            if ($current_app_name !== $new_app_name) {
                Mozilla\WebAppManifestGenerator::getInstance()->set_field('short_name', $new_app_name['value']);
            }
            return $new_app_name;
        }

        $new_app_name['value'] = trim($new_app_name['value']);
        if (empty($new_app_name['value'])) {
            add_settings_error(
                $this->options-o('app-name'),
                'bad-app-name',
                __('The name must not be empty.', 'add-to-homescreen')
            );
            return $current_app_name;
        }

        if ($current_app_name !== $new_app_name) {
            Mozilla\WebAppManifestGenerator::getInstance()->set_field('short_name', $new_app_name['value']);
        }
        return $new_app_name;
    }

    private function check_app_name_integrity($app_name) {
        return is_array($app_name) &&
               array_key_exists('type', $app_name) &&
               array_key_exists('value', $app_name);
    }

    private function add_illegal_app_name_error() {
        add_settings_error(
            $this->options->o('app-name'),
            'illegal-app-name',
            __('Incorrect application name.', 'add-to-homescreen')
        );
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

    public function regenerate_icon($old_value, $new_value) {
        if ($old_value !== $new_value) {
            WP_Serve_File::getInstance()->invalidate_files(array('add2home.svg'));
        }
    }

}

?>
