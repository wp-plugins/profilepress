<?php
/**
             * ProfilePress ajax requests
             *
             * @package   ProfilePress
             * @author    Rahul Aryan <rah12@live.com>
             * @license   GPL-3.0+
             * @link      http://wp3.in
             * @copyright 2014 Rahul Aryan
             */

class PP_Ajax
{

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
        add_action('pp_ajax_edit_pp_field', array($this, 'edit_pp_field'));
        add_action('pp_ajax_cancel_update_field', array($this, 'cancel_update_field'));
        add_action('pp_ajax_pp_upload', array($this, 'avatar_upload'));
        add_action('pp_ajax_favorite', array($this, 'favorite'));
    }

    

    /**
     * load edit pp field form
     * @return void
     * @since 0.0.1
     */
    public function edit_pp_field() {
        $pp_user_obj = pp_current_user_object();

        if (!isset($_POST['field']) || !is_user_logged_in()) {
            pp_send_json('something_wrong');
        }

        $field_id = sanitize_text_field($_POST['field']);
        $action = 'field_'.$field_id.'_'.get_current_user_id();
    	
        if ($field_id == 'name') {
            ob_start();
            ?>
    		<div class="field-label">
    			<label class="meta-fields-label" for="__pp_field_name"><?php _e('Name', 'pp') ?></label>
    		</div>
    		<div class="field-value">
    			<input id="__pp_field_first_name" name="__pp_field_first_name" class="field-input" value="<?php echo get_user_meta(get_current_user_id(), 'first_name', true) ?>" placeholder="<?php _e('First name', 'pp') ?>" /> 
    			<input id="__pp_field_last_name" name="__pp_field_last_name" class="field-input" value="<?php echo get_user_meta(get_current_user_id(), 'last_name', true) ?>" placeholder="<?php _e('Last name', 'pp') ?>" /> 
    		</div>
    		<?php
            $form = ob_get_clean();
        } elseif ($field_id == 'description') {
            ob_start();
            ?>
    		<div class="field-label">
    			<label class="meta-fields-label" for="__pp_field_description"><?php _e('About me', 'pp') ?></label>
    		</div>
    		<div class="field-value">
    			<textarea rows="6" id="__pp_field_description" name="__pp_field_description" class="field-input" placeholder="<?php _e('About me', 'pp') ?>"><?php echo get_user_meta(get_current_user_id(), 'description', true) ?></textarea>
    		</div>
    		<?php
            $form = ob_get_clean();
        } elseif ($field_id == 'nickname') {
            ob_start();
            ?>
    		<div class="field-label">
    			<label class="meta-fields-label" for="__pp_field_nickname"><?php _e('Nick name', 'pp') ?></label>
    		</div>
    		<div class="field-value">
    			<input id="__pp_field_nickname" name="__pp_field_nickname" class="field-input" value="<?php echo get_user_meta(get_current_user_id(), 'nickname', true) ?>" placeholder="<?php _e('Nick name', 'pp') ?>" />
    		</div>
    		<?php
            $form = ob_get_clean();
        } elseif ($field_id == 'display_name') {
            $first_name = pp_current_user_meta('first_name', true);
            $last_name = pp_current_user_meta('last_name', true);
            $nickname = pp_current_user_meta('nickname', true);
            $public_display = array();
            $public_display[$nickname] = $nickname;
            $public_display[$pp_user_obj->data->user_login] = $pp_user_obj->data->user_login;

            if (!empty($first_name)) {
                $public_display[$first_name] = $first_name;
            }
    		
            if (!empty($last_name)) {
                $public_display[$last_name] = $last_name;
            }
    		
            if (!empty($first_name) && !empty($last_name)) {
                $public_display[$first_name.' '.$last_name] = $first_name.' '.$last_name;
                $public_display[$last_name.' '.$first_name] = $last_name.' '.$first_name;
            }

            ob_start();
            ?>
    		<div class="field-label">
    			<label class="meta-fields-label" for="__pp_field_display_name"><?php _e('Display name', 'pp') ?></label>
    		</div>
    		<div class="field-value">
    			<select name="__pp_field_display_name" id="__pp_field_display_name" class="field-input">
    				<?php 
    				
                    $value = $pp_user_obj->data->display_name;

                    foreach ($public_display as $name) {
                        echo '<option value="'.$name.'" '.selected($value, $name, false).'>'.$name.'</option>';
                    }
                    ?>
    			</select>
    		</div>
    		<?php
            $form = ob_get_clean();
        } else {
            $field_id = (int) $_POST['field'];
            $field = pp_get_field($field_id);
    		
            ob_start();
            pp_field_type_form($field, get_current_user_id());
            $form = ob_get_clean();
    		
        }

        ob_start();

        echo '<form data-action="pp_ajax_form" class="clearfix">';
        echo $form;
        $nonce = wp_create_nonce('field_cancel_'.$field_id);
        echo '<a class="pp-btn light-color" href="#" data-action="cancel_update_field" data-query="field='.$field_id.'&pp_ajax_action=cancel_update_field&__nonce='.$nonce.'">'.__('cancel', 'pp').'</a>';
        echo '<input type="submit" value="'.__('ok', 'pp').'" class="pp-btn" />';
        echo '<input type="hidden" name="pp_ajax_action" value="update_user_meta_field" />';
        echo '<input type="hidden" name="pp_form_action" value="update_user_meta_field" />';
        echo '<input type="hidden" name="field" value="'.$field_id.'" />';
        echo '<input type="hidden" name="__nonce" value="'.wp_create_nonce($action).'" />';
        echo '</form>';
    	
        $html = ob_get_clean();

        $result = array(
            'action' 	=> 'edit_pp_field',
            'message' 	=> 'success',
            'container' => '[data-cont="field_'.$field_id.'"]',
            'do' 		=> 'replace',
            'html' 		=> $html,
            );

        pp_send_json($result);
    }

    public function cancel_update_field()
    {
        $field_name = (int) $_POST['field'];

        if (!isset($_POST['field']) || !isset($_POST['__nonce']) || !is_user_logged_in()) {
            pp_send_json(array('message' => 'something_wrong'));
            return;
        }

        $nonce_action = 'field_cancel_'.$field_name;

        if (!wp_verify_nonce($_POST['__nonce'], $nonce_action)) {
            pp_send_json(array('message' => 'something_wrong'));
            return;
        }

        $field = pp_get_field($field_name);

        if (!$field) {
            pp_send_json(array('message' => 'something_wrong'));
            return;
        }

        ob_start();
        pp_field_type_view($field, get_current_user_id());
        $html = ob_get_clean();

        $result = array(
            'action' 	=> 'cancel_update_field',
            'message' 	=> 'success',
            'container' => '[data-cont="field_'.$field->ID.'"]',
            'do' 		=> 'replace',
            'html' 		=> $html,
            );

        pp_send_json($result);
    }

    public function upload_file() {
        require_once(ABSPATH."wp-admin".'/includes/image.php');
        require_once(ABSPATH."wp-admin".'/includes/file.php');
        require_once(ABSPATH."wp-admin".'/includes/media.php');
        if ($_FILES) {
            foreach ($_FILES as $file => $array) {
                if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
                    echo "upload error : ".$_FILES[$file]['error'];
                    die();
                }
                return  media_handle_upload($file, 0);
            }   
        }
    }

    /**
     * Upload and update user avatar
     * @return void
     */
    public function avatar_upload()
    {

        if (!wp_verify_nonce($_POST['__nonce'], 'pp_upload') || !is_user_logged_in()) {
            pp_send_json(array('message' => 'something_wrong'));
            return;
        }

        $attach_id = $this->upload_file();

        $userid = get_current_user_id();  

        $previous_avatar = get_user_meta($userid, '__pp_avatar', true);

        wp_delete_attachment($previous_avatar, true);

        update_user_meta($userid, '__pp_avatar', $attach_id);

        $result = array(
            'action'    => 'avatar_upload',
            'message'   => 'avatar_uploaded',
            'container' => '[data-cont="avatar_'.$userid.'"]',
            'do'        => 'updateAvatar',
            'html'      => get_avatar($userid, pp_opt('main_avatar_size')),
            );

        pp_send_json($result);
    }

    public function favorite()
    {

        if (!wp_verify_nonce($_POST['__nonce'], 'favorite_'.(int) $_POST['p_id'])) {
            pp_send_json(array('message' => 'something_wrong'));
            return;
        }

        if (!is_user_logged_in()) {
            pp_send_json(array('message' => 'please_login'));
            return;
        }

        $p_id = (int) $_POST['p_id'];

        $is_favorited = wp3_is_user_favorited($p_id); 
        $userid = get_current_user_id();   

        if ($is_favorited) {
            // if already subscribed then remove    
            $row = wp3_remove_vote('favorite', $userid, $p_id);
            
            $counts = wp3_post_favorites_count($p_id);
            
            //update post meta
            update_post_meta($p_id, '__favorites', $counts);
            
            //register an action
            do_action('pp_removed_favorite', $p_id, $counts);

            pp_send_json(
                array(
                    'message'   => 'removed_from_favorite', 
                    'action'    => 'removed_from_favorite',
                    'do'        => 'replace removeClass',
                    'container' => '#favorite_'.$p_id,
                    'html'      => '<span>'.__('Add to favorite', 'pp').'</span>',
                    'class'     => 'active'
                    )
                );
            return;

        } else {
            $row = wp3_add_vote($userid, 'favorite', $p_id);

            $counts = wp3_post_favorites_count($p_id);
            
            //update post meta
            update_post_meta($p_id, '__favorites', $counts);
            
            //register an action
            do_action('pp_added_favorite', $p_id, $counts);
            
            pp_send_json(
                array(
                    'message'   => 'added_to_favorite', 
                    'action'    => 'added_to_favorite',
                    'do'        => 'replace addClass',
                    'container' => '#favorite_'.$p_id,
                    'html'      => '<span>'.__('Remove from favorite', 'pp').'</span>',
                    'class'     => 'active'
                    )
                );
        }

    }
}
