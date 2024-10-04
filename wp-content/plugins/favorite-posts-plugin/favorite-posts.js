jQuery(document).ready(function($) {
    $('.favorite-button').click(function() {
        var postId = $(this).data('post-id'); // Obtém o ID do post a partir do data attribute
        var button = $(this); // Armazena a referência ao botão clicado

        $.ajax({
            method: 'POST',
            url: wpApiSettings.root + 'favorite-posts/v1/toggle/' + postId, // URL da API
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce); // Adiciona o nonce para segurança
            },
            success: function(response) {
                if (response.status === 'favorited') {
                    button.text('Desfavoritar'); // Atualiza o texto do botão
                } else {
                    button.text('Favoritar'); // Atualiza o texto do botão
                }
            },
            error: function(error) {
                console.log('Erro:', error); // Loga qualquer erro
            }
        });
    });
});

add_action('wp_enqueue_scripts', 'enqueue_favorite_script');

jQuery(document).ready(function($) {
    $('.favorite-button').click(function() {
        var postId = $(this).data('post-id');
        var button = $(this);

        $.ajax({
            method: 'POST',
            url: wpApiSettings.root + 'favorite-posts/v1/toggle/' + postId,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
            success: function(response) {
                if (response.status === 'favorited') {
                    button.text('Desfavoritar');
                    button.removeClass('favoritar').addClass('desfavoritar'); // Altera a classe
                } else {
                    button.text('Favoritar');
                    button.removeClass('desfavoritar').addClass('favoritar'); // Altera a classe
                }
            },
            error: function(error) {
                console.log('Erro:', error);
            }
        });
    });
});