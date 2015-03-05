<?php
/**
     * User profile for WordPress
     *
     * ProfilePress adds user pp page
     *
     * @package   ProfilePress
     * @author    Rahul Aryan <rah12@live.com>
     * @copyright 2014 WP3.in & Rahul Aryan
     * @license   GPL-3.0+ http://www.gnu.org/licenses/gpl-3.0.txt
     * @link      http://wp3.in
     *
     * @wordpress-plugin
     * Plugin Name:       ProfilePress
     * Plugin URI:        http://wp3.in
     * Description:       Simple user profile for WordPress
     * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20ProfilePress%20development
     * Version:           0.0.6
     * Author:            Rahul Aryan
     * Author URI:        http://wp3.in
     * Text Domain:       pp
     * License:           GPL-3.0+
     * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
     * Domain Path:       /languages
     */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!class_exists('ProfilePress')) {

    /**
     * Main ProfilePress class
     * @package ProfilePress
     */
    class ProfilePress
    {

        private $_plugin_version = '0.0.6';

        private $_plugin_path;

        private $_plugin_url;

        private $_text_domain = 'pp';

        public static $instance = null;

        public $_hooks;
        public $pp_ajax;

        /**
         * Filter object
         * @var object
         */
        public $pp_query_filter;

        /**
         * Theme object
         * @var object
         * @since 2.0.1
         */
        public $_theme;

        /**
         * Fields object
         * @var object
         * @since 2.0.1
         */
        public $_fields;
        public $_ajax;
        public $_cpt;
        public $_meta_boxes;

        public $_pp_forms;

        /**
         * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
         * @return instance
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof ProfilePress)) {
                self::$instance = new ProfilePress();
                self::$instance->_setup_constants();
                
                add_action('init', array(self::$instance, 'load_textdomain'));

                global $pp_classes;
                $pp_classes = array();

                self::$instance->includes();

                self::$instance->_hooks              = new PP_Hooks();
                self::$instance->_fields             = new PP_Custom_Fields();
                self::$instance->_pp_forms      = new PP_Process_Form();
                self::$instance->_ajax               = new PP_Ajax();
                self::$instance->_cpt                = new PP_CPT();
                self::$instance->_meta_boxes         = new PP_Meta_Boxes();
                self::$instance->_theme              = new PP_Theme();

                /**
                 * ACTION: pp_loaded
                 * Hooks for extension to load their codes after ProfilePress is leaded
                 */
                do_action('pp_loaded');
            }

            return self::$instance;
        }

            /**
             * Setup plugin constants
             *
             * @access private
             * @since  2.0.1
             * @return void
             */
            private function _setup_constants()
            {
                if (!defined('PROFILE_VERSION')) {
                    define('PROFILE_VERSION', $this->_plugin_version);
                }

                if (!defined('PROFILE_DIR')) {
                    define('PROFILE_DIR', plugin_dir_path(__FILE__));
                }

                if (!defined('PROFILE_URL')) {
                    define('PROFILE_URL', plugin_dir_url(__FILE__));
                }

                if (!defined('PROFILE_THEME_DIR')) {
                    define('PROFILE_THEME_DIR', PROFILE_DIR.'/theme');
                }

                if (!defined('PROFILE_THEME_URL')) {
                    define('PROFILE_THEME_URL', PROFILE_URL.'/theme');
                }




            }

        /**
         * Include required files
         *
         * @access private
         * @since 2.0.1
         * @return void
         */
        private function includes()
        {
            global $pp_options;

            require_once PROFILE_DIR.'includes/options.php';
            require_once PROFILE_DIR.'includes/functions.php';
            require_once PROFILE_DIR.'includes/theme-functions.php';
            require_once PROFILE_DIR.'includes/roles-permission.php';
            require_once PROFILE_DIR.'includes/activate.php';
            require_once PROFILE_DIR.'includes/hooks.php';
            require_once PROFILE_DIR.'includes/ajax.php';
            require_once PROFILE_DIR.'includes/process-form.php';
            require_once PROFILE_DIR.'includes/cpt.php';
            require_once PROFILE_DIR.'includes/meta_boxes.php';
            require_once PROFILE_DIR.'includes/class-theme.php';
            require_once PROFILE_DIR.'includes/meta.php';
            require_once PROFILE_DIR.'includes/shortcode-basepage.php';            
            require_once PROFILE_DIR.'includes/class-form.php';
            require_once PROFILE_DIR.'includes/class-validation.php';
            require_once PROFILE_DIR.'includes/fields.php';
            require_once PROFILE_DIR.'includes/class-fields.php';
            require_once PROFILE_DIR.'includes/views.php';
            require_once PROFILE_DIR.'includes/rewrite.php';
        }

        /**
         * Load translations
         *
         * @access private
         * @since 2.0.1
         * @return void
         */
        public function load_textdomain()
        {
            load_plugin_textdomain($this->_text_domain, false, dirname(plugin_basename(__FILE__)).'/languages/');
        }
    }
}

function pp()
{
    ProfilePress::instance();
}

pp();

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook(__FILE__, 'pp_activate');

//register_deactivation_hook(__FILE__, array( 'pp', 'deactivate' ));


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * The code below is intended to to give the lightest footprint possible.
 */

if (is_admin()) {
    require_once plugin_dir_path(__FILE__).'admin/admin.php';
    add_action('plugins_loaded', array('pp_admin', 'get_instance'));
}
