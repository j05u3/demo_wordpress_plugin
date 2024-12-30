<?php
/**
 * Altoke Plugin
 *
 * @package     AltokePlugin
 * @wordpress-plugin
 * Plugin Name: Altoke Plugin
 * Description: Altoke Plugin para gestionar datos y APIs REST.
 * Version:     1.0.1
 * Author:      Team 1
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

// add action to upload an image
add_action("wp_ajax_altoke_upload_image", "altoke_upload_image_function");
add_action("wp_ajax_nopriv_altoke_upload_image", "altoke_upload_image_function");

function altoke_upload_image_function()
{
  // Verify nonce for security
  // check_ajax_referer('altoke_upload_image_nonce', 'nonce');

  if (!isset($_FILES['image'])) {
    wp_send_json_error(array('message' => 'No image file provided'));
    return;
  }

  $image = $_FILES['image'];

  // Handle the image upload
  require_once(ABSPATH . 'wp-admin/includes/image.php');
  require_once(ABSPATH . 'wp-admin/includes/file.php');
  require_once(ABSPATH . 'wp-admin/includes/media.php');

  $attachment_id = media_handle_upload('image', 0);

  if (is_wp_error($attachment_id)) {
    wp_send_json_error(array('message' => $attachment_id->get_error_message()));
  } else {
    $attachment_url = wp_get_attachment_url($attachment_id);
    wp_send_json_success(array('url' => $attachment_url, 'id' => $attachment_id));
  }
}

add_action("wp_ajax_altoke_get_user_data", "altoke_get_user_data_function");
add_action("wp_ajax_nopriv_altoke_get_user_data", "altoke_get_user_data_function");

function altoke_get_user_data_function() {
    // Get current user
    $user = wp_get_current_user();
    
    if (!$user->exists()) {
        wp_send_json_error(array('message' => 'User not logged in'));
        return;
    }

    // Get user data using UM's functions
    $user_id = $user->ID;
    
    // Note that this function uses WordPress's built-in get_user_meta() function 
    // to retrieve the Ultimate Member custom fields, which should work fine since Ultimate Member stores its data in WordPress user meta.
    $user_data = array(
        'nombre_apellido' => $user->user_login,
        'email' => $user->user_email,
        'dni' => get_user_meta($user_id, 'dniNumber', true),
        'whatsapp' => get_user_meta($user_id, 'numberPhone', true),
        'codigo_promocional' => get_user_meta($user_id, 'codigoPromocional', true)
    );

    wp_send_json_success($user_data);
}

// Agregar las acciones para la API REST
add_action("wp_ajax_altoke_guardar_dni_urls", "altoke_guardar_dni_urls_function");
add_action("wp_ajax_nopriv_altoke_guardar_dni_urls", "altoke_guardar_dni_urls_function");

function altoke_guardar_dni_urls_function() {
    // Verificar que el usuario esté logueado
    $user = wp_get_current_user();
    
    if (!$user->exists()) {
        wp_send_json_error(array('message' => 'Usuario no logueado'));
        return;
    }

    // Verificar si las URLs fueron enviadas
    if (!isset($_POST['dniFrontUrl']) || !isset($_POST['dniBackUrl'])) {
        wp_send_json_error(array('message' => 'Faltan las URLs de las imágenes del DNI'));
        return;
    }

    // Obtener las URLs del DNI frontal y posterior
    $dniFrontUrl = sanitize_text_field($_POST['dniFrontUrl']);
    $dniBackUrl = sanitize_text_field($_POST['dniBackUrl']);

    // Guardar las URLs en los metadatos del usuario
    $user_id = $user->ID;
    update_user_meta($user_id, 'dniFrontUrl', $dniFrontUrl);
    update_user_meta($user_id, 'dniBackUrl', $dniBackUrl);

    // Enviar respuesta de éxito
    wp_send_json_success(array('message' => 'URLs de imágenes del DNI guardadas correctamente'));
}
// Agregar acción para guardar la URL de la foto de perfil
add_action("wp_ajax_altoke_guardar_perfil_dni", "altoke_guardar_perfil_dni_function");
add_action("wp_ajax_nopriv_altoke_guardar_perfil_dni", "altoke_guardar_perfil_dni_function");

function altoke_guardar_perfil_dni_function() {
    // Verificar que el usuario esté logueado
    $user = wp_get_current_user();
    
    if (!$user->exists()) {
        wp_send_json_error(array('message' => 'Usuario no logueado'));
        return;
    }

    // Verificar si la URL de la imagen fue enviada
    if (!isset($_POST['profileDNIUrl'])) {
        wp_send_json_error(array('message' => 'Falta la URL de la imagen del perfil'));
        return;
    }

    // Obtener la URL de la imagen del perfil
    $profileDNIUrl = sanitize_text_field($_POST['profileDNIUrl']);
    
    // Verificar que la URL sea válida (opcional, puedes omitir esta validación si ya confías en la imagen subida)
    if (empty($profileDNIUrl) || !filter_var($profileDNIUrl, FILTER_VALIDATE_URL)) {
        wp_send_json_error(array('message' => 'La URL de la imagen no es válida'));
        return;
    }

    // Obtener el ID de la imagen a partir de la URL
    $attachment_id = attachment_url_to_postid($profileDNIUrl);

    if (!$attachment_id) {
        wp_send_json_error(array('message' => 'No se pudo obtener el ID de la imagen'));
        return;
    }

    // Guardar la URL de la imagen en los metadatos del usuario
    $user_id = $user->ID;
    update_user_meta($user_id, 'profile_image_url', $profileDNIUrl);
    update_user_meta($user_id, 'profile_image_id', $attachment_id);  // Guardamos también el ID del attachment si lo necesitas.

    // Enviar respuesta de éxito
    wp_send_json_success(array('message' => 'URL de la imagen de perfil guardada correctamente'));
}

// Agregar acción para guardar las URLs de las imágenes de las tarjetas
add_action("wp_ajax_altoke_guardar_tarjeta_urls", "altoke_guardar_tarjeta_urls_function");
add_action("wp_ajax_nopriv_altoke_guardar_tarjeta_urls", "altoke_guardar_tarjeta_urls_function");

function altoke_guardar_tarjeta_urls_function() {
    // Verificar que el usuario esté logueado
    $user = wp_get_current_user();
    
    if (!$user->exists()) {
        wp_send_json_error(array('message' => 'Usuario no logueado'));
        return;
    }

    // Verificar si las URLs de las imágenes de las tarjetas fueron enviadas
    if (!isset($_POST['cards'])) {
        wp_send_json_error(array('message' => 'No se han recibido las imágenes de las tarjetas'));
        return;
    }

    // Obtener las URLs de las imágenes de las tarjetas
    $cardsJson = stripslashes($_POST['cards']);
    $cards = json_decode($cardsJson, true);
    if (!$cards) {
        wp_send_json_error(array('message' => 'Error al decodificar las URLs de las tarjetas'));
        return;
    }

    error_log("URLs de las tarjetas recibidas: " . print_r($cards, true));

    // Guardar las URLs en los metadatos del usuario
    $user_id = $user->ID;

    // Serializar las URLs para almacenarlas como un array
    $cards_data = [];
    foreach ($cards as $card) {
        $cards_data[] = [
            'bank' => sanitize_text_field($card['bank']),
            'cardImage' => sanitize_text_field($card['cardImage'])
        ];
    }

    // Guardar las URLs de las tarjetas en los metadatos del usuario
    update_user_meta($user_id, 'card_images_urls', $cards_data);

    // Enviar respuesta de éxito
    wp_send_json_success(array('message' => 'URLs de las tarjetas guardadas correctamente'));
}


// Agregar acción para guardar las cuentas de ahorro del usuario
add_action("wp_ajax_altoke_guardar_cuentas_ahorro", "altoke_guardar_cuentas_ahorro_function");
add_action("wp_ajax_nopriv_altoke_guardar_cuentas_ahorro", "altoke_guardar_cuentas_ahorro_function");

function altoke_guardar_cuentas_ahorro_function() {
    // Verificar que el usuario esté logueado
    $user = wp_get_current_user();
    
    if (!$user->exists()) {
        wp_send_json_error(array('message' => 'Usuario no logueado'));
        return;
    }

    // Verificar si las cuentas de ahorro fueron enviadas
    if (!isset($_POST['accounts'])) {
        wp_send_json_error(array('message' => 'No se han recibido las cuentas de ahorro'));
        return;
    }

    // Obtener las cuentas de ahorro
    $accountsJson = stripslashes($_POST['accounts']);
    $accounts = json_decode($accountsJson, true);
    if (!$accounts) {
        wp_send_json_error(array('message' => 'Error al decodificar las cuentas de ahorro'));
        return;
    }

    error_log("Cuentas de ahorro recibidas: " . print_r($accounts, true));

    // Guardar las cuentas de ahorro en los metadatos del usuario
    $user_id = $user->ID;

    // Eliminar las cuentas anteriores antes de guardar las nuevas
    delete_user_meta($user_id, 'deposit_accounts'); // Actualizado para usar 'deposit_accounts'

    // Guardar las nuevas cuentas
    update_user_meta($user_id, 'deposit_accounts', $accounts); // Actualizado para usar 'deposit_accounts'

    // Enviar respuesta de éxito
    wp_send_json_success(array('message' => 'Cuentas de ahorro guardadas correctamente'));
}
add_action("wp_ajax_altoke_get_all_users_data", "altoke_get_all_users_data_function");
add_action("wp_ajax_nopriv_altoke_get_all_users_data", "altoke_get_all_users_data_function");

function altoke_get_all_users_data_function() {
    $args = array(
        'orderby' => 'ID',
        'order' => 'DESC'
    );
    $users = get_users($args);
    $users_data = [];

    foreach ($users as $user) {
        $user_id = $user->ID;
        $users_data[] = array(
            'id' => $user_id,
            'nombre_apellido' => get_user_meta($user_id, 'nickname', true),
            'dni' => get_user_meta($user_id, 'dniNumber', true) ?: 'No disponible',
            'whatsapp' => get_user_meta($user_id, 'numberPhone', true) ?: 'No disponible'
        );
    }

    wp_send_json_success($users_data);
}
add_action("wp_ajax_altoke_get_user_data_new", "altoke_get_user_data_function_new");
add_action("wp_ajax_nopriv_altoke_get_user_data_new", "altoke_get_user_data_function_new");

function altoke_get_user_data_function_new() {
    // Obtener el ID del usuario desde la solicitud AJAX
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($user_id === 0) {
        wp_send_json_error(array('message' => 'ID del usuario no proporcionado o no válido'));
        return;
    }

    // Verificar si el usuario existe
    $user = get_userdata($user_id);
    
    if (!$user) {
        wp_send_json_error(array('message' => 'Usuario no encontrado'));
        return;
    }

    // Obtener datos del usuario usando los meta campos de Ultimate Member
    $user_data = array(
        'nombre_apellido' => get_user_meta($user_id, 'nickname', true),
        'email' => $user->user_email,
        'dni' => get_user_meta($user_id, 'dniNumber', true) ?: 'No disponible',
        'whatsapp' => get_user_meta($user_id, 'numberPhone', true) ?: 'No disponible',
        'codigo_promocional' => get_user_meta($user_id,'codigoPromocional', true) ?: 'No disponible',
        'dniFrontUrl' => get_user_meta($user_id, 'dniFrontUrl', true) ?: 'No disponible',
        'dniBackUrl' => get_user_meta($user_id, 'dniBackUrl', true) ?: 'No disponible',
        'profile_image_url' => get_user_meta($user_id, 'profile_image_url', true) ?: 'No disponible',
        'card_images_urls' => get_user_meta($user_id, 'card_images_urls', true) ?: 'No disponible'
    );

    wp_send_json_success($user_data);
}
