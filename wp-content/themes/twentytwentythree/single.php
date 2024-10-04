<?php get_header(); ?>

<?php
if (have_posts()) :
    while (have_posts()) : the_post();
        the_title('<h1>', '</h1>');
        the_content();

        // Botão de favoritar
        $post_id = get_the_ID();
        $favorites = get_user_favorites(get_current_user_id());
        $is_favorited = in_array($post_id, $favorites) ? 'Desfavoritar' : 'Favoritar';
        echo '<button class="favorite-button" data-post-id="' . $post_id . '">' . $is_favorited . '</button>';

    endwhile;
endif;
?>

<?php get_footer(); ?>
