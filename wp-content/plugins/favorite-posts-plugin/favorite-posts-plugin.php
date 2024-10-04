<?php
/*
Plugin Name: Favorite Posts Plugin
Description: Um plugin para favoritar posts no WordPress.
Version: 1.0
Author: Karine Pêgo
*/

// Função que será chamada quando o plugin for ativado
register_activation_hook(__FILE__, 'create_favorite_table');

// Função para criar a tabela no banco de dados
function create_favorite_table() {
    global $wpdb; // Acessa o objeto global $wpdb, usado para interagir com o banco de dados

    // Nome da tabela no banco de dados
    $table_name = $wpdb->prefix . 'favorite_posts';

    // Charset do banco de dados (define a codificação de caracteres e collation)
    $charset_collate = $wpdb->get_charset_collate();

    // SQL para criar a tabela
    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        post_id bigint(20) UNSIGNED NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY user_post (user_id, post_id)
    ) $charset_collate;";

    // Inclui o arquivo upgrade.php, necessário para usar a função dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    // Executa o SQL de criação da tabela
    dbDelta($sql);
}

// Registra os endpoints da REST API
add_action('rest_api_init', function () {
    register_rest_route('favorite-posts/v1', '/toggle/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'toggle_favorite_post',
        'permission_callback' => function () {
    $is_logged_in = is_user_logged_in();
    error_log('User is logged in: ' . ($is_logged_in ? 'true' : 'false'));
    return $is_logged_in; // Retorna se o usuário está logado
},
    ));
});



// Função para alternar entre favoritar e desfavoritar um post
function toggle_favorite_post($request) {
    global $wpdb; // Acessa o objeto global $wpdb
    $user_id = get_current_user_id(); // Obtém o ID do usuário logado
    $post_id = $request['id']; // Obtém o ID do post do request

    $table_name = $wpdb->prefix . 'favorite_posts'; // Nome da tabela

    // Verifica se o post já foi favoritado
    $favorite = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id, $post_id
    ));

    if ($favorite) {
        // Se já foi favoritado, desfavorita
        $wpdb->delete($table_name, array('user_id' => $user_id, 'post_id' => $post_id));
        return rest_ensure_response(array('status' => 'unfavorited'));
    } else {
        // Caso contrário, favoritar
        $wpdb->insert($table_name, array('user_id' => $user_id, 'post_id' => $post_id));
        return rest_ensure_response(array('status' => 'favorited'));
    }
}

/*
function toggle_favorite_post($request) {
    $post_id = (int) $request['id'];
    $user_id = get_current_user_id();

    // Lógica para adicionar/remover do favorito
    if (is_user_logged_in()) {
        $favorites = get_user_meta($user_id, 'favorite_posts', true) ?: [];

        if (in_array($post_id, $favorites)) {
            $favorites = array_diff($favorites, [$post_id]);
            $status = 'unfavorited';
        } else {
            $favorites[] = $post_id;
            $status = 'favorited';
        }

        update_user_meta($user_id, 'favorite_posts', $favorites);

        return new WP_REST_Response(['status' => $status], 200);
    }

    return new WP_Error('not_logged_in', 'Você precisa estar logado para favoritar posts.', ['status' => 401]);
}*/


// Enfileira o script jQuery e passa o nonce para o JavaScript
add_action('wp_enqueue_scripts', 'enqueue_custom_nonce');

function enqueue_custom_nonce() {
    // Garante que o jQuery está sendo carregado
    wp_enqueue_script('jquery'); 
    
    // Passa o nonce para o JavaScript através de uma variável chamada customData
    wp_localize_script('jquery', 'customData', array(
        'nonce' => wp_create_nonce('wp_rest') // Cria o nonce para as requisições REST
    ));
}



function get_user_favorites($user_id) {
    global $wpdb; // Acessa o objeto global $wpdb para interagir com o banco de dados
    $table_name = $wpdb->prefix . 'favorite_posts'; // Define o nome da tabela de posts favoritos

    // Consulta ao banco de dados para obter os IDs dos posts favoritados pelo usuário
    $favorite_posts = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM $table_name WHERE user_id = %d",
        $user_id
    ));

    // Verifica se a consulta retornou algum resultado
    if ($favorite_posts) {
        return $favorite_posts; // Retorna os IDs dos posts favoritados
    }

    return []; // Retorna um array vazio se o usuário não tiver favoritado nenhum post
}



// Adiciona o widget de favoritos ao WordPress
class Favorite_Posts_Widget extends WP_Widget {

    // Configurações do widget
    function __construct() {
        parent::__construct(
            'favorite_posts_widget',
            __('Meus Favoritos', 'text_domain'),
            array('description' => __('Exibe os posts favoritados pelo usuário', 'text_domain'))
        );
    }

    // Exibe o conteúdo do widget na barra lateral
    public function widget($args, $instance) {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $favorites = get_user_favorites($user_id);

            echo $args['before_widget'];
            echo $args['before_title'] . __('Meus Posts Favoritos', 'text_domain') . $args['after_title'];

            if (!empty($favorites)) {
                echo '<ul>';
                foreach ($favorites as $post_id) {
                    echo '<li><a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></li>';
                }
                echo '</ul>';
            } else {
                echo 'Nenhum post favoritado.';
            }

            echo $args['after_widget'];
        }
    }
}

// Registra o widget no WordPress
function register_favorite_posts_widget() {
    register_widget('Favorite_Posts_Widget');
}

add_action('widgets_init', 'register_favorite_posts_widget');


// Função para obter os favoritos do usuário
function get_user_favorites_shortcode() {
    if (!is_user_logged_in()) {
        return 'Você precisa estar logado para ver seus posts favoritos.';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'favorite_posts';

    // Obtém os IDs dos posts favoritados pelo usuário
    $favorite_posts = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM $table_name WHERE user_id = %d",
        $user_id
    ));

    if (empty($favorite_posts)) {
        return 'Você ainda não favoritou nenhum post.';
    }

    // Cria a lista de links para os posts favoritos
    $output = '<ul>';
    foreach ($favorite_posts as $post_id) {
        $post_title = get_the_title($post_id);
        $post_url = get_permalink($post_id);
        $output .= "<li><a href='{$post_url}'>{$post_title}</a></li>";
    }
    $output .= '</ul>';

    return $output;
}

// Registra o shortcode [user_favorites]
add_shortcode('user_favorites', 'get_user_favorites_shortcode');


// Enfileira o script jQuery para a funcionalidade de favoritar posts
add_action('wp_enqueue_scripts', 'enqueue_favorite_script');

function enqueue_favorite_script() {
    wp_enqueue_script('favorite-posts-script', plugins_url('/favorite-posts.js', __FILE__), array('jquery'), null, true);

    wp_localize_script('favorite-posts-script', 'favoriteData', array(
        'nonce' => wp_create_nonce('wp_rest'), // Passa o nonce para o script
        'ajax_url' => rest_url('favorite-posts/v1/toggle/') // URL da API para favoritar
    ));
}






add_action('wp_enqueue_scripts', function() {
    // Enfileira o script JavaScript
    wp_enqueue_script('favorite-posts-script', plugin_dir_url(__FILE__) . 'favorite-posts.js', array('jquery'), null, true);
    
    // Passa a URL da API e o nonce para o JavaScript
    wp_localize_script('favorite-posts-script', 'wpApiSettings', array(
        'root' => esc_url_raw(rest_url()), // URL base da API REST
        'nonce' => wp_create_nonce('wp_rest') // Cria o nonce
    ));
});

function favorite_button_shortcode() {
    $post_id = get_the_ID(); // ID do post atual
    $is_favorited = get_user_meta(get_current_user_id(), 'favorite_posts', true); // Recupera os posts favoritados do usuário
    $is_favorited = $is_favorited ? explode(',', $is_favorited) : []; // Converte a string em um array

    // Verifica se o post está favoritado
    $button_text = in_array($post_id, $is_favorited) ? 'Desfavoritar' : 'Favoritar';
    $button_class = in_array($post_id, $is_favorited) ? 'desfavoritar' : 'favoritar';

    return '<button class="favorite-button ' . $button_class . '" data-post-id="' . $post_id . '">' . $button_text . '</button>';
}
add_shortcode('favorite_button', 'favorite_button_shortcode');


// Adiciona o CSS para estilizar os botões de Favoritar/Desfavoritar
function favorite_posts_styles() {
    echo '<style>
        .favorite-button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            color: white;
            background-color: black;
            border-radius: 5px;
        }
    </style>';
}

add_action('wp_head', 'favorite_posts_styles');
