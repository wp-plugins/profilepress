<?php

/**
 * This file contains theme script, styles and other theme related functions.
 *
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author    Rahul Aryan <rah12@live.com>
 */

/**
 * Enqueue scripts
 *
 */
add_action('wp_enqueue_scripts', 'init_pp_assets', 11);
function init_pp_assets() {
    ?>
    <script type="text/javascript"> var ajaxurl = '<?php
        echo admin_url('admin-ajax.php'); ?>'; </script>
        <?php
        wp_enqueue_script('pp_script', PROFILE_URL . 'assets/pp_site.js', 'jquery', PROFILE_VERSION);
        
        wp_localize_script('pp_script', 'pplang', array(
            'password_field_not_macthing' => __('Password not matching', 'pp') ,
            'error' => '',
            ));
        
        wp_enqueue_style('pp-fonts', pp_get_theme_url('fonts/style.css') , array() , PROFILE_VERSION);
        wp_enqueue_style('pp-style', pp_get_theme_url('css/main.min.css') , array() , PROFILE_VERSION);
        
        //if (is_profilepress()) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-form', array(
                'jquery'
                ) , false, true);
            wp_enqueue_script('tooltipster', pp_get_theme_url('js/jquery.tooltipster.min.js') , 'jquery', PROFILE_VERSION);
            wp_enqueue_script('pp-theme-js', pp_get_theme_url('js/pp-theme.js') , 'jquery', PROFILE_VERSION);
            wp_enqueue_script('initial', pp_get_theme_url('js/initial.min.js') , 'jquery', PROFILE_VERSION);
            do_action('pp_enqueue');
            wp_enqueue_style('pp-overrides', pp_get_theme_url('css/overrides.css') , array() , PROFILE_VERSION);
       // }
    }

/*add_action( 'widgets_init', 'pp_widgets_positions' );
function pp_widgets_positions(){
	register_sidebar( array(
		'name'         	=> __( 'AP Before', 'ap' ),
		'id'           	=> 'ap-before',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' 	=> '</div>',
		'description'  	=> __( 'Widgets in this area will be shown before anspress body.', 'ap' ),
		'before_title' 	=> '<h3 class="ap-widget-title">',
		'after_title'  	=> '</h3>',
	) );

}*/
