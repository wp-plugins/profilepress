<?php
/**
     * ProfilePresss
     *
     * @package   ProfilePress
     * @author    Rahul Aryan <rah12@live.com>
     * @license   GPL-3.0+
     * @link      http://wp3.in
     * @copyright 2015 Rahul Aryan
     */

/**
 * PP_Admin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package ProfilePress
 * @author  Rahul Aryan <admin@rahularyan.com>
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
 
class PP_Admin {

    /**
     * Instance of this class.	 
     * @var      object
     */
    protected static $instance = null;

    protected $plugin_slug = 'pp';

    /**
     * Slug of the plugin screen.	 
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;
	
    // Name of the array
    protected $option_name = 'pp_opt';

	
    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     */
    private function __construct() {
		
        $this->includes();

        add_action('wp_loaded', array($this, 'load_options_form'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add the options page and menu item.
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));

        // Add an action link pointing to the options page.
        $plugin_basename = plugin_basename(plugin_dir_path(__DIR__).$this->plugin_slug.'.php');
        add_filter('plugin_action_links_'.$plugin_basename, array($this, 'add_action_links'));
		
        add_action('admin_init', array($this, 'register_setting'));
		
        // flush rewrite rule if option updated
        add_action('admin_init', array($this, 'init_actions'));
		
        add_action('parent_file', array($this, 'tax_menu_correction'));
		
		
        add_action('wp_ajax_pp_save_options', array($this, 'pp_save_options'));
        add_action('wp_ajax_pp_field_type_options', array($this, 'pp_field_type_options'));
		
    }

    public function load_options_form()
    {
        PP_Options_Page::add_option_groups();
    }

    /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function includes()
    {
        require_once('functions.php'); 
        require_once('options-page.php'); 
    }

    /**
     * Register and enqueue admin-specific style sheet.
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style('pp-admin-css', PROFILE_URL.'assets/css/pp_admin.css');
        wp_enqueue_style('pp-iconfonts', pp_get_theme_url('fonts/style.css'));
        wp_enqueue_style("jquery-ui-css", "http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css");
    }

    /**
     * Register and enqueue admin-specific JavaScript.
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_scripts() {
        global $typenow, $pagenow;

        wp_enqueue_script('jquery-form', array('jquery'), false, true);

        wp_enqueue_style('jquery-ui-datepicker');

        wp_enqueue_script('pp-admin-js', PROFILE_URL.'assets/pp_admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'));
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     */
    public function add_plugin_admin_menu() {
		
        $pos = $this->get_free_menu_position(50, 0.3);
		
        add_menu_page('ProfilePress', 'ProfilePress'.$Totalcount, 'manage_options', 'pp', array($this, 'dashboard_page'), 'dashicons-profile_logo', $pos);
        add_submenu_page('pp', __('All fields', 'pp'), __('All fields', 'pp'), 'manage_options', 'edit.php?post_type=pp_field', '');
        add_submenu_page('pp', __('New profile field', 'pp'), __('New profile field', 'pp'), 'manage_options', 'post-new.php?post_type=pp_field', '');
        add_submenu_page('pp', __('Profile field groups', 'pp'), __('Profile field groups', 'pp'), 'manage_options', 'edit-tags.php?taxonomy=pp_group', '');
        add_submenu_page('pp', __('ProfilePress Options', 'pp'), __('Options', 'pp'), 'manage_options', 'pp_options', array($this, 'display_plugin_admin_page'));
		
		
        /**
         * ACTION: pp_admin_menu
         * @since unknown
         */
        do_action('pp_admin_menu');		
		
    }
	
    /**
     * @param integer $start
     */
    public function get_free_menu_position($start, $increment = 0.3) {
        $menus_positions = array();
        foreach ($GLOBALS['menu'] as $key => $menu) {
            $menus_positions[] = $key;
        }
 
        if (!in_array($start, $menus_positions)) {
            return $start;
        }
 
        /* the position is already reserved find the closet one */
        while (in_array($start, $menus_positions)) {
            $start += $increment;
        }
        return $start;
    }
	
    // highlight the proper top level menu
    public function tax_menu_correction($parent_file) {
        global $current_screen;
        $taxonomy = $current_screen->taxonomy;
        if ($taxonomy == 'pp_group') {
                    $parent_file = 'pp';
        }
        return $parent_file;
    }

    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_admin_page() {
        include_once('views/admin.php');
    }
	
    public function dashboard_page() {
        include_once('views/dashboard.php');
    }

    /**
     * Add settings action link to the plugins page.
     */
    public function add_action_links($links) {

        return array_merge(
            array(
                'settings' => '<a href="'.admin_url('admin.php?page=pp_options').'">'.__('Settings', 'pp').'</a>'
            ),
            $links
        );

    }
    //register settings
    public function register_setting() {
        register_setting('pp_points', 'pp_points', array($this, 'validate_options'));
    }
    public function validate_options($input) {
        return $input;
    }
    public function init_actions() {

        $GLOBALS['wp']->add_query_var('post_parent');
		
        // flush_rules if option updated	
        if (isset($_GET['page']) && ('pp_options' == $_GET['page']) && isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            $options = pp_opt();			
            $page = get_page(pp_opt('base_page'));
            $options['base_page_slug'] = $page->post_name;
            update_option('pp_opt', $options);
            pp_opt('pp_flush', 'true');
        }
    }

    public function pp_save_options() {		
        if (current_user_can('manage_options')) {
            $result = array();
            flush_rewrite_rules();
            $options = $_POST['pp_opt'];

            if (!empty($options) && is_array($options)) {
                $old_options = get_option('pp_opt');
				
                foreach ($options as $k => $opt) {
                    $old_options[$k] = $opt;
                }

                update_option('pp_opt', $old_options);

                $result = array('status' => true, 'html' => '<div class="updated fade" style="display:none"><p><strong>'.__('Options updated successfully', 'pp').'</strong></p></div>');
            }
				
            die(json_encode($result));
        }
		
    }

    public function pp_field_type_options() {		
        if (current_user_can('manage_options')) {
            $type = sanitize_text_field($_GET['type']);
            $post_id = (int) $_GET['post_id'];

            $post = get_post($post_id);
			
            pp_field_option_by_type($type, $post);
				
            die();
        }
		
    }
	
}
