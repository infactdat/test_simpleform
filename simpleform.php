<?php
/**
 * Plugin Name: Simple form
 * Plugin URI:  https://www.infact1.co.jp/
 * Description: This is original form of Infact corporation
 * Author:      Infact
 * Author URI:  https://www.infact1.co.jp/
 * Version:     1.1
 * Text Domain: wpforms
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Don't allow multiple versions to be active
if (class_exists('SimpleForm')) {

    /**
     * Deactivate if Form already activated.
     *
     * @since 1.0.0
     */
    function wpforms_deactivate()
    {

        deactivate_plugins(plugin_basename(__FILE__));
    }

    add_action('admin_init', 'wpforms_deactivate');

    

} else {

    /**
     * Main Form class.
     *
     * @since 1.0.0
     *
     * @package Form
     */
    final class SimpleForm
    {

        /**
         * One is the loneliest number that you'll ever do.
         *
         * @since 1.0.0
         *
         * @var object
         */
        private static $instance;

        /**
         * Plugin version for enqueueing, etc.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $version = '1.4.3';

        /**
         * The form data handler instance.
         *
         * @since 1.0.0
         *
         * @var object Form_Form_Handler
         */
        public $form;

        /**
         * The entry data handler instance
         *
         * @since 1.0.0
         *
         * @var object Form_Entry_Handler
         */
        public $entry;

        /**
         * The entry fields data handler instance
         *
         * @since 1.0.0
         *
         * @var object Form_Entry_Fields_Handler
         */
        public $entry_fields;

        /**
         * The entry meta data handler instance
         *
         * @since 1.1.6
         *
         * @var object Form_Entry_Meta_Handler
         */
        public $entry_meta;

        /**
         * The front-end instance.
         *
         * @since 1.0.0
         *
         * @var object Form_Frontend
         */
        public $frontend;

        /**
         * The process instance.
         *
         * @since 1.0.0
         *
         * @var object Form_Process
         */
        public $process;

        /**
         * The smart tags instance.
         *
         * @since 1.0.0
         *
         * @var object Form_Smart_Tags
         */
        public $smart_tags;

        /**
         * The Logging instance.
         *
         * @since 1.0.0
         *
         * @var object Form_Logging
         */
        public $logs;

        /**
         * The Preview instance.
         *
         * @since 1.1.9
         *
         * @var object Form_Preview
         */
        public $preview;

        /**
         * The License class instance
         *
         * @since 1.0.0
         *
         * @var object Form_License
         */
        public $license;

        /**
         * Paid returns true, free (Lite) returns false.
         *
         * @since 1.3.9
         *
         * @var boolean
         */
        public $pro = false;

        /**
         * Main Infact form Instance.
         *
         * Insures that only one instance of Form exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 1.0.0
         *
         * @return
         */
        public static function instance()
        {

            if (!isset(self::$instance) && !(self::$instance instanceof SimpleForm)) {

                self::$instance = new SimpleForm();
                self::$instance->constants();
                self::$instance->load_textdomain();
                self::$instance->conditional_logic_addon_check();
                self::$instance->includes();
                require_once WPFORMS_PLUGIN_DIR . 'advance/advance-functions.php';
                add_action('plugins_loaded', array(self::$instance, 'objects'), 10);
            }

            return self::$instance;
        }

        /**
         * Setup plugin constants.
         *
         * @since 1.0.0
         */
        private function constants()
        {

            // Plugin version.
            if (!defined('WPFORMS_VERSION')) {
                define('WPFORMS_VERSION', $this->version);
            }

            // Plugin Folder Path.
            if (!defined('WPFORMS_PLUGIN_DIR')) {
                define('WPFORMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            // Plugin Folder URL.
            if (!defined('WPFORMS_PLUGIN_URL')) {
                define('WPFORMS_PLUGIN_URL', plugin_dir_url(__FILE__));
            }

            // Plugin Root File.
            if (!defined('WPFORMS_PLUGIN_FILE')) {
                define('WPFORMS_PLUGIN_FILE', __FILE__);
            }

            // Plugin Slug - Determine plugin type and set slug accordingly.
            $this->pro = true;
            define('WPFORMS_PLUGIN_SLUG', 'wpforms');

        }

        /**
         * Loads the plugin language files.
         *
         * @since 1.0.0
         */
        public function load_textdomain()
        {

            load_plugin_textdomain('wpforms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * Check to see if the conditional logic addon is running, if so then
         * deactivate the plugin to prevent conflicts.
         *
         * @since 1.0.0
         */
        private function conditional_logic_addon_check()
        {

            if (function_exists('wpforms_conditional_logic')) {

                // Load core files needed to activate deactivate_plugins().
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                require_once(ABSPATH . 'wp-includes/pluggable.php');

                // Deactivate Conditional Logic addon.
                deactivate_plugins('wpforms-conditional-logic/wpforms-conditional-logic.php');

                // To avoid namespace collisions, reload current page.
                $url = esc_url_raw(remove_query_arg('wpforms-test'));
                wp_redirect($url);
                exit;
            }
        }

        /**
         * Include files.
         *
         * @since 1.0.0
         */
        private function includes()
        {

            // Global includes.
            require_once WPFORMS_PLUGIN_DIR . 'includes/functions.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-install.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-form.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-fields.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-frontend.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-templates.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-providers.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-process.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-smart-tags.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-logging.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-preview.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/class-conditional-logic-core.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/emails/class-emails.php';
            require_once WPFORMS_PLUGIN_DIR . 'includes/smtp/wp-smtp.php';

            // Admin/Dashboard only includes.
            if (is_admin()) {
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/admin.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-notices.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-menu.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/overview/class-overview.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/builder/class-builder.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/builder/functions.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-settings.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-editor.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-importers.php';
                require_once WPFORMS_PLUGIN_DIR . 'includes/admin/ajax-actions.php';
            }


        }

        /**
         * Setup objects.
         *
         * @since 1.0.0
         */
        public function objects()
        {

            // Global objects.
            $this->form = new WPForms_Form_Handler;
            $this->frontend = new WPForms_Frontend;
            $this->process = new WPForms_Process;
            $this->smart_tags = new WPForms_Smart_Tags;
            $this->logs = new WPForms_Logging;
            $this->preview = new WPForms_Preview;


            // Hook now that all of the Infact form stuff is loaded.
            do_action('wpforms_loaded');
        }
    }

    function wpforms()
    {

        return SimpleForm::instance();

    }

    wpforms();

}


