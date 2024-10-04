<?php
// Silence is golden.

if (is_user_logged_in()) : ?>
    <button class="favorite-button" data-post-id="<?php the_ID(); ?>">
        <?php
        // Verifica se o post está favoritado pelo usuário
        $user_id = get_current_user_id();
        $is_favorited = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}favorite_posts WHERE user_id = %d AND post_id = %d",
            $user_id, get_the_ID()
        ));
        echo $is_favorited ? 'Desfavoritar' : 'Favoritar';
        ?>
    </button>
<?php endif; ?>
