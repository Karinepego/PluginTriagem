<?php
/*
Plugin Name: Favorite Posts Plugin
Description: Um plugin para favoritar posts no WordPress.
Version: 1.0
Author: Karine Pêgo
*/

register_activation_hook(__FILE__, 'create_favorite_table'); // executada quando o plugin é ativado. 

function create_favorite_table() {
    global $wpdb; // me permitir interagir com o banco

    $table_name = $wpdb->prefix . 'favorite_posts'; //prefixo wb_ la no myphpadmin
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        post_id bigint(20) UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY user_post (user_id, post_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Registra uma nova rota (endpoint) da REST API 
add_action('rest_api_init', function () {
    register_rest_route('favorite-posts/v1', '/toggle/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'toggle_favorite_post',
        'permission_callback' => function () { //so permite usuário logado faça favoritação 
            return is_user_logged_in();
        },
    ));
});

// Função para alternar entre favoritar e desfavoritar um post
function toggle_favorite_post($request) {
    global $wpdb;
    $user_id = get_current_user_id();
    $post_id = $request['id'];

    $table_name = $wpdb->prefix . 'favorite_posts';

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

// Enfileira o script jQuery e passa o nonce para o JavaScript
add_action('wp_enqueue_scripts', 'enqueue_custom_nonce');

function enqueue_custom_nonce() {
    wp_enqueue_script('jquery'); 
    wp_localize_script('jquery', 'customData', array(
        'nonce' => wp_create_nonce('wp_rest')
    ));
}

// Função para obter os favoritos do usuário
function get_user_favorites($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'favorite_posts';

    // Consulta ao banco de dados para obter os IDs dos posts favoritados pelo usuário
    $favorite_posts = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM $table_name WHERE user_id = %d",
        $user_id
    ));

    return $favorite_posts ? $favorite_posts : []; 
}

// Adiciona o widget de favoritos ao WordPress
class Favorite_Posts_Widget extends WP_Widget {
    function __construct() {
        parent::__construct(
            'favorite_posts_widget',
            __('Meus Favoritos', 'text_domain'),
            array('description' => __('Exibe os posts favoritados pelo usuário', 'text_domain'))
        );
    }

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

function register_favorite_posts_widget() {
    register_widget('Favorite_Posts_Widget');
}

add_action('widgets_init', 'register_favorite_posts_widget');

// Função para obter os favoritos do usuário via shortcode
function get_user_favorites_shortcode() {
    if (!is_user_logged_in()) {
        return 'Você precisa estar logado para ver seus posts favoritos.';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'favorite_posts';

    // Obtém os IDs dos posts favoritados pelo usuário
    $favorite_posts = get_user_favorites($user_id);

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
        'nonce' => wp_create_nonce('wp_rest'),
        'ajax_url' => rest_url('favorite-posts/v1/toggle/')
    ));
}

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

// Shortcode para o botão de favoritar
function favorite_button_shortcode() {
    $post_id = get_the_ID(); 
    $user_id = get_current_user_id(); 
    $is_favorited = get_user_favorites($user_id); // Utiliza a função get_user_favorites

    // Verifica se o post está favoritado
    $button_text = in_array($post_id, $is_favorited) ? 'Desfavoritar' : 'Favoritar';
    $button_class = in_array($post_id, $is_favorited) ? 'desfavoritar' : 'favoritar';

    return '<button class="favorite-button ' . $button_class . '" data-post-id="' . $post_id . '">' . $button_text . '</button>';
}
add_shortcode('favorite_button', 'favorite_button_shortcode');
