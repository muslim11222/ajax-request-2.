<?php

/**
 * Plugin name: Ajax Requests Plugin
 * Description: This is an AJAX request plugin
 * Author: Muslim Khan
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class arp_ajax_requests {
    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    public function init() {
        add_shortcode('simple-auth', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'load_scripts'));
        add_action('wp_ajax_simple_auth_profile_form', array($this, 'update_profile'));
        add_action('wp_ajax_nopriv_simple_auth_profile_form', array($this, 'update_profile')); // Handle for non-logged in users if needed
        
        //login
        add_action('wp_ajax_nopriv_simple-auth-login-form', array($this, 'handle_login')); 
    }

  //login
  public function handle_login() {
    check_ajax_referer('simple-auth-login', '_wpnonce');

    $username =  sanitize_text_field($_POST['username']);
    $password = sanitize_text_field($_POST['password']);

    $user = wp_signon([
        'user_login' => $username,
        'user_password' => $password,
        'remember' => false,
    ]);

    if (is_wp_error($user)) {
        wp_send_json_error([
            'message' => $user->get_error_message(),
        ]);
    }

    wp_send_json_success([
        'message' => 'login successfully',
    ]);

   
  }




    //render_shortcode
    public function render_shortcode() {
        if ( is_user_logged_in() ) {
            return $this->render_profile_page();
        } else {
            return $this->render_auth_page();
        }
    }
      
    //render_profile_page
     public function render_profile_page() {
        $user = wp_get_current_user();
        ob_start();
        ?>
            <div id="simple-auth-profile">

               <h2> Update Profile </h2>

               <div id="profile-update-message"></div>
               <form method="post" id="profile-form">

                    <input type="text" name="display_name" value="<?php echo esc_attr($user->display_name); ?>">
                    <input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>">
                    <input type="hidden" name="action" value="simple_auth_profile_form"/>

                    <?php wp_nonce_field('simple-auth-nonce', '_wpnonce'); ?>
                    <button type="submit"> Update Profile </button>
               </form>
            </div>



        <?php
        return ob_get_clean();
     }


     //render_auth_page
    public function render_auth_page() {
        $user = wp_get_current_user();
        ob_start();
        ?>

            <div id="simple-auth-profile">

               <h2> login </h2>

               <div id="login-message"></div>

                <form method="post" id="simple-auth-login-form">

                    <input type="text" name="username" value="" placeholder="Enter your username">
                    <input type="password" name="password" value="" placeholder="Enter your password">
                    <input type="hidden" name="action" value="simple_auth_login_form"/>

                    <?php wp_nonce_field('simple-auth-login', '_wpnonce'); ?>

                    <button type="submit"> login </button>
               </form>

            </div>



        <?php
        return ob_get_clean();
       
    }
   
    //load_scripts
        public function load_scripts() {
        $ajax_path = plugin_dir_url(__FILE__);
        $js_path = $ajax_path . 'js/';
        wp_enqueue_script('script_load', $js_path . 'ajax.js', ['jquery', 'wp-util'], '1.0.0', true);
        wp_localize_script('script_load', 'simpleAuthAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('simple-auth-nonce'),
        ));

        $css_file_path = $ajax_path . 'css/';
        wp_enqueue_style('style_load', $css_file_path . 'ajax.css', [], '1.0.2');
    }


    //update_profile
    public function update_profile() {
        check_ajax_referer('simple-auth-nonce', '_wpnonce');

        $display_name = sanitize_text_field($_POST['display_name']);
        $email = sanitize_email($_POST['email']);

        $user_data = [
            'ID' => get_current_user_id(),
            'display_name' => $display_name,
            'user_email' => $email,
        ];

        $user_id = wp_update_user($user_data);

        if (is_wp_error($user_id)) {
            wp_send_json_error([
                'message' => $user_id->get_error_message(),
            ]);
        }

        wp_send_json_success([
            'message' => 'Profile updated successfully',
        ]);
    }
}

new arp_ajax_requests();
