<?php
// Silence is golden.

<?php if (is_user_logged_in()) : ?>
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


<script>
jQuery(document).ready(function($) {
    $('#favorite-button').on('click', function() {
        var isFavorited = $(this).data('favorited');
        var action = isFavorited ? 'remove_favorite' : 'add_favorite';

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: action,
                post_id: '<?php echo $post_id; ?>',
            },
            success: function(response) {
                if (response.success) {
                    // Atualiza o texto do botão
                    $('#favorite-button').text(isFavorited ? 'Favoritar' : 'Desfavoritar');
                    $('#favorite-button').data('favorited', isFavorited ? 0 : 1);
                } else {
                    alert('Ocorreu um erro. Tente novamente.');
                }
            },
            error: function() {
                alert('Ocorreu um erro. Tente novamente.');
            }
        });
    });
});
</script>
