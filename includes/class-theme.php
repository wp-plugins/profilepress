<?php

/**
 * ProfilePress theme class
 *
 * @link http://wp3.in
 * @since 0.0.1
 * @package ProfilePress
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class PP_Theme
{
    public function __construct() {
        
        pp_register_user_page('profile', __('Profile', 'pp'), array($this, 'profile_page'));
        pp_register_user_page('about', __('About', 'pp'), array($this, 'pp_about'));
        
        pp_register_user_page('favorites', __('Favorites', 'pp'), array($this, 'page_favorites'));
        pp_register_user_page('posts', __('Posts', 'pp'), array($this, 'page_my_posts'));

        if(defined('AP_VERSION')){
            pp_register_user_page('questions', __('Questions', 'pp'), array($this, 'page_questions'));
            pp_register_user_page('answers', __('Answers', 'pp'), array($this, 'page_answers'));
        }

        
        add_action('init', array($this, 'theme_function') );
        add_filter('wp_title', array($this, 'wp_title'), 100, 2);
        add_filter('the_title', array($this, 'the_title'), 100, 2);
        
        add_filter('pp_blocks', array($this, 'users_qa'));
        add_filter('pp_blocks', array($this, 'user_posts'));
        add_filter('ap_post_actions_buttons', array($this, 'post_action_favorite_button'));
    }
    
    public function profile_page() {
        include (pp_get_theme_location('profile.php'));
    }
    
    public function pp_about() {
        include (pp_get_theme_location('about.php'));
    }
    
    public function page_my_posts() {
        include (pp_get_theme_location('posts.php'));
    }
    
    public function page_favorites() {
        include (pp_get_theme_location('favorites.php'));
    }

    public function theme_function()
    {
        add_shortcode('profilepress', array(PP_BasePage_Shortcode::get_instance(), 'pp_sc'));
        require_once pp_get_theme_location('functions.php');
    }

    public function page_questions()
    {
        require_once pp_get_theme_location('questions.php');
    }

    public function page_answers()
    {
        require_once pp_get_theme_location('answers.php');
    }
    
    /**
     * @param string $title
     * @return void
     */
    public function wp_title($title) {
        if (is_profilepress()) {
            $new_title = pp_page_title();
            
            $new_title = str_replace('PROFILE_TITLE', $new_title, $title);
            $new_title = apply_filters('profile_title', $new_title);
            
            return $new_title;
        }
        
        return $title;
    }
    
    public function the_title($title, $id) {
        if ($id == pp_opt('base_page')) {
            return pp_page_title();
        }
        return $title;
    }
    
    public function users_qa($user_id) {
        if(!defined('AP_VERSION'))
            return;

        $args = array('post_type' => array('question', 'answer'), 'author' => pp_get_current_user(), 'showposts' => 5, 'post_status' => 'publish');
        
        $posts = new WP_Query($args);
        
        if ($posts->have_posts()) {
            ?>
            <h3 class="pp-list-head">
                <?php
                _e('Questions & Answers', 'pp') ?>
                <span class="user-post-count">(<?php
                    echo count_user_posts(pp_get_current_user(), 'question') + count_user_posts(pp_get_current_user(), 'answer') ?>)</span>
                </h3>
                <?php
                while ($posts->have_posts()):
                    $posts->the_post();
                include pp_get_theme_location('content-question.php');
                endwhile;
                echo '<div class="show-all">' . __('Show all', 'pp') . ' <a href="' . pp_get_link_to('answers') . '">' . __('answers by', 'pp') . '</a> ' . pp_display_name(pp_get_current_user()) . ' &rarr; <span>  |  </span>' . __('Show all', 'pp') . ' <a href="' . pp_get_link_to('questions') . '">' . __('questions by', 'pp') . '</a> ' . pp_display_name(pp_get_current_user()) . ' &rarr;</div>';
            } 
            else {
                _e('No question and answer posted by this user.', 'pp');
            }
            wp_reset_postdata();
    }

    public function user_posts($user_id) {
        $args = array('post_type' => 'post', 'author' => pp_get_current_user(), 'showposts' => 5, 'post_status' => 'publish');
        
        $posts = new WP_Query($args);
        
        if ($posts->have_posts()) {
            ?>
            <h3 class="pp-list-head">
                <?php
                _e('Posts', 'pp') ?>
                <span class="user-post-count">(<?php
                    echo count_user_posts(pp_get_current_user(), 'post') ?>)</span>
                </h3>
                <?php
                while ($posts->have_posts()):
                    $posts->the_post();
                include pp_get_theme_location('content-post.php');
                endwhile;
                echo '<div class="show-all">' . __('Show all', 'pp') . ' <a href="' . pp_get_link_to('posts') . '">' . __('posts by', 'pp') . '</a> ' . pp_display_name(pp_get_current_user()) . ' &rarr;</div>';
            } 
            else {
                _e('No posts were written by this user.', 'pp');
            }
            wp_reset_postdata();
    }

    public function post_action_favorite_button($metas)
    {
        $metas['favorite'] = wp3_favorite_btn(get_the_ID());

        return $metas;
    }
}
