<?php
/**
 * Plugin acivation hook
 *
 * Things to do after activating pp plugin
 *
 * @link http://wp3.in
 * @since 0.0.1
 *
 * @package ProfilePress
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

function pp_activate($network_wide) {

    // add roles
    //$ap_roles = new PROFILE_Roles;
    //$ap_roles->add_roles();
    //$ap_roles->add_capabilities();
    
    
    global $wpdb;

        
    // check if page already exists
    $page_id = pp_opt("base_page");
    
    $post = get_post($page_id);
    
    if (!$post) {
        $args = array();
        $args['post_type']          = "page";
        $args['post_content']       = "[profilepress]";
        $args['post_status']        = "publish";
        $args['post_title']         = "PROFILE_TITLE";
        $args['post_name']          = "pp";
        $args['comment_status']     = 'closed';
        
        // now create post
        $new_page_id = wp_insert_post($args);
    
        if ($new_page_id) {
            $page = get_post($new_page_id);
            pp_opt("base_page", $page->ID);
            pp_opt("base_page_id", $page->post_name);
        }
    }

    
    
    
    if (pp_opt('version') != PROFILE_VERSION) {
        pp_opt('installed', false);
        pp_opt('version', PROFILE_VERSION);
    }
    
    /**
     * Run DB quries only if PROFILE_DB_VERSION does not match
     */
    if (pp_opt('db_version') != PROFILE_DB_VERSION) {   
    
        $charset_collate = !empty($wpdb->charset) ? "DEFAULT CHARACTER SET ".$wpdb->charset : '';

        $meta_table = "CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."ap_meta` (
                  `pp_id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `pp_userid` bigint(20) DEFAULT NULL,
                  `pp_type` varchar(256) DEFAULT NULL,
                  `pp_actionid` bigint(20) DEFAULT NULL,
                  `pp_value` text,
                  `pp_param` LONGTEXT DEFAULT NULL,
                  `pp_date` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`pp_id`)
                )".$charset_collate.";";
        
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        dbDelta($meta_table);
        pp_opt('db_version', PROFILE_DB_VERSION);
    }

    
    if (!get_option('pp_opt')) {
            update_option('pp_opt', pp_default_options());
    } else {
            update_option('pp_opt', get_option('pp_opt') + pp_default_options());
    }
        
    
    pp_opt('ap_flush', 'true'); 
    flush_rewrite_rules(false);
}
