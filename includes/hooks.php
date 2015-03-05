<?php

/**
 * Actions and filters of pp
 *
 * @link http://wp3.in
 * @since 0.0.1
 * @package ProfilePress
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class PP_Hooks
{
    public function __construct() {
        new PP_Rewrite();
        add_filter('query_vars', array( $this, 'query_vars' ));
        add_filter('get_avatar', array( $this, 'get_avatar' ) , 10, 5);
        add_action('the_post', array( $this, 'insert_views' ));
        add_filter('the_content', array( $this, 'add_favorite_button' ));
        add_action('posts_clauses', array( $this, 'user_favorites' ) , 10, 2);       
    }
    
    public function query_vars($vars) {
        $vars[] = 'pp_page';
        $vars[] = 'pp_user';
        $vars[] = 'pp_user_login';
        $vars[] = 'pp_cpt';
        
        return $vars;
    }
    
    /**
     * Override get_avatar
     * @param  string $avatar
     * @param  integar|string $id_or_email
     * @param  string $size
     * @param  string $default
     * @param  string $alt
     * @return string
     */
    public function get_avatar($avatar, $id_or_email, $size, $default, $alt) {

        if (!empty($id_or_email)) {

            if (is_object($id_or_email)) {
                $allowed_comment_types = apply_filters('get_avatar_comment_types', array(
                    'comment'
                    ));
                if (!empty($id_or_email->comment_type) && !in_array($id_or_email->comment_type, (array)$allowed_comment_types)) {
                    return $avatar;
                }

                if (!empty($id_or_email->user_id)) {
                    $id          = (int)$id_or_email->user_id;
                    $user        = get_userdata($id);
                    if ($user) {
                        $id_or_email = $user->ID;
                    }
                }else{
                    $id_or_email = 0;
                }
            } 
            elseif (is_email($id_or_email)) {
                $u           = get_user_by('email', $id_or_email);
                $id_or_email = $u->ID;
            }

            
            $resized     = pp_get_avatar($id_or_email, $size);
            
            if ($resized) {
                return "<img data-cont='avatar_{$id_or_email}' alt='{$alt}' src='{$resized}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            } 
            else {
                $display_name = pp_display_name(array(
                    'html' => false,
                    'user_id' => $id_or_email
                    ));
                return '<img data-cont="avatar_' . $id_or_email . '" alt="' . $alt . '" data-name="' . $display_name . '" data-height="' . $size . '" data-width="' . $size . '" data-char-count="2" class="pp-dynamic-avatar"/>';
            }
        }
    }
    
    public function insert_views($post) {
        if (is_profilepress() && pp_get_current_user() != 0) {

            if (!wp3_is_already_viewed(get_current_user_id() , pp_get_current_user())) wp3_insert_pp_views();
        }
    }
    
    public function add_favorite_button($content) {
        remove_filter('the_content', array(
            $this,
            'add_favorite_button'
            ));
        
        if (!is_singular(array_flip(pp_opt('favorite_cpt')))) return $content;
        
        $btn = wp3_favorite_btn(get_the_ID());
        
        return $content . $btn;
    }
    
    public function user_favorites($sql, $query) {
        global $wpdb;
        if (isset($query->query['pp_query']) && $query->query['pp_query'] == 'user_favorites') {
            $sql['join'] = 'LEFT JOIN ' . $wpdb->prefix . 'ap_meta apmeta ON apmeta.apmeta_actionid = ID ' . $sql['join'];
            $sql['where'] = 'AND apmeta.apmeta_userid = post_author AND apmeta.apmeta_type ="favorite" ' . $sql['where'];
        }
        return $sql;
    }
}
