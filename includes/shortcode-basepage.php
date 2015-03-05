<?php
/**
 * Class for ProfilePress base page shortcode
 *
 * @package   ProfilePress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

class PP_BasePage_Shortcode {

    protected static $instance = NULL;

    public static function get_instance()
    {
        // create an object
        NULL === self::$instance and self::$instance = new self;

        return self::$instance; // return the object
    }

    /**
     * Control the output of [pp] shortcode
     * @param  array $atts
     * @param  string $content
     * @return string
     * @since 2.0.0-beta
     */
    public function pp_sc( $atts, $content="" ) {
		
        global $questions;
		
        //$this->init();

        //$questions = $this->questions;

        ob_start();
        echo '<div class="pp-container">';
			
            /**
             * ACTION: ap_before
             * Action is fired before loading ProfilePress body.
             */
            do_action('ap_before');
			
            // include theme file
            pp_page();

			
        echo '</div>';
        return ob_get_clean();
        wp_reset_postdata();
    }
	
}

