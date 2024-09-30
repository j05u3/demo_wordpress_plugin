<?php
/**
 * Altoke Plugin
 *
 * @package     AltokePlugin
 * @author      Team 1
 * @copyright   2024 Team 1
 * @license     BUSD
 *
 * @wordpress-plugin
 * Plugin Name: Altoke Plugin
 * Plugin URI:  https://mehdinazari.com/how-to-create-hello-world-plugin-for-wordpress
 * Description: Altoke Plugin
 * Version:     1.0.1
 * Author:      Team 1
 * Author URI:  https://mehdinazari.com
 * Text Domain: hello-world
 * License:     BUSD
 * License URI: https://opensource.org/licenses/BSD-3-Clause
 */

add_action("wp_ajax_altoke_get_user_profile", "altoke_get_user_profile_function");
add_action("wp_ajax_nopriv_altoke_get_user_profile", "altoke_get_user_profile_function");

function altoke_get_user_profile_function()
{
  // Verify nonce for security
  // check_ajax_referer('altoke_user_profile_nonce', 'nonce');

  // Example error handling
  // if (/* some error condition */) {
  //     wp_send_json_error(array('message' => 'An error occurred'));
  //     return;
  // }

  // use wp_get_current_user to get the user name
  $userName = wp_get_current_user()->user_login;

  $someInput = $_POST['some_input'];

  wp_send_json_success(array(
    'user_name' => $userName,
    'some_input' => $someInput,
  ));
}