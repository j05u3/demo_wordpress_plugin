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
 */

// Registrar la ruta REST API
add_action('rest_api_init', function () {
    error_log('Registrando la ruta REST: /custom/v1/get-user-data');
    register_rest_route('custom/v1', '/get-user-data', array(
        'methods' => 'GET',
        'callback' => 'get_user_data',
        'permission_callback' => '__return_true',
    ));
});

// Callback para la API REST
function get_user_data(WP_REST_Request $request) {
    error_log('Ejecutando el callback para /custom/v1/get-user-data');
    $current_user = wp_get_current_user();

    if ($current_user->ID === 0) {
        error_log('El usuario no está logueado');
        return new WP_REST_Response(['error' => 'User not logged in'], 401);
    }

    // Recuperar datos del usuario
    $user_meta = [
        'nombre_apellido' => $current_user->user_login,
        'correo' => $current_user->user_email,
        'dni' => get_user_meta($current_user->ID, 'dniNumber', true),
        'whatsapp' => get_user_meta($current_user->ID, 'numberPhone', true),
        'codigo_promocional' => get_user_meta($current_user->ID, 'codigoPromocional', true),
    ];

    error_log('Datos del usuario recuperados: ' . print_r($user_meta, true));

    return new WP_REST_Response($user_meta, 200);
}

// Código para listar las rutas REST registradas (temporal)
add_action('init', function () {
    global $wp_rest_server;
    $wp_rest_server = rest_get_server();
    echo '<pre>';
    print_r($wp_rest_server->get_routes());
    echo '</pre>';
    die();
});
