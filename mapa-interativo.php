<?php

/**
 * Plugin Name: Mapa Interativo
 * Version: 0.0.1
 * Author: ER Soluções Web LTDA
 * Author URI: https://ersolucoesweb.com.br
 */

/**
 * Carregar os script
 */

add_action('wp_enqueue_scripts', function () {
    // scripts para adicionar
    wp_enqueue_style('mapa-interativo', plugin_dir_url(__FILE__) . 'dist/app.css', [], time());
    wp_enqueue_script('mapa-interativo-vendors', plugin_dir_url(__FILE__) . 'dist/chunk-vendors.js', [], time());
    wp_enqueue_script('mapa-interativo', plugin_dir_url(__FILE__) . 'dist/app.js', [], time());
});

register_activation_hook(__FILE__, function(){
    $cidades = json_decode(file_get_contents(__DIR__ . '/cidades.json'));
    foreach($cidades as $cidade) {
        wp_insert_term( $cidade, 'cidade');
    }
});

/**
 * How to use
 * 
 * Example: [mapa_interativo]
 * 
 * Exemplo PHP: do_shortcode('[mapa_interativo]')
 */
add_shortcode('mapa_interativo', function () {
    return '<div id="mapa-interativo">carregando...</div>';
});


add_action('rest_api_init', function () {
    $is_admin = current_user_can('administrator');
    register_rest_route('mapa-interativo/v1', '/is_admin', array(
        'methods' => 'GET',
        'permission_callback' => function () use ($is_admin) {
            return true;
        },
        'callback' => function (WP_REST_Request $request) use ($is_admin) {
            return $is_admin;
        },
    ));
    register_rest_route('mapa-interativo/v1', '/unidades', array(
        'methods' => 'GET',
        'permission_callback' => function () use ($is_admin) {
            return true;
        },
        'callback' => function (WP_REST_Request $request) use ($is_admin) {
            if($cities = get_transient('mi_cidades')) return $cities;
            $args = array('taxonomy' => 'cidade', 'hide_empty' => false);
            $cidades = get_terms($args);
            set_transient('mi_cidades', $cidades, HOUR_IN_SECONDS);
            return $cidades;
        },
    ));
    register_rest_route('mapa-interativo/v1', '/markers', array(
        'methods' => 'GET',
        'permission_callback' => function () use ($is_admin) {
            return true;
        },
        'callback' => function (WP_REST_Request $request) use ($is_admin) {
            return get_option('mapa-interativo', []);
        },
    ));
    register_rest_route('mapa-interativo/v1', '/markers', array(
        'methods' => 'POST',
        'permission_callback' => function () use ($is_admin) {
            return $is_admin;
        },
        'callback' => function (WP_REST_Request $request) use ($is_admin) {
            return update_option('mapa-interativo', json_decode(file_get_contents('php://input')));
        },
    ));
});
