jQuery(document).ready(function($) {
    $('.favorite-button').click(function() {
        var postId = $(this).data('post-id'); // Obtém o ID do post a partir do data attribute
        var button = $(this); // Armazena a referência ao botão clicado

        $.ajax({
            method: 'POST',
            url: favoriteData.ajax_url + postId, // Corrigido para usar favoriteData.ajax_url
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', favoriteData.nonce); // Adiciona o nonce para segurança
            },
            success: function(response) {
                if (response.status === 'favorited') {
                    button.text('Desfavoritar'); // Atualiza o texto do botão
                    button.removeClass('favoritar').addClass('desfavoritar'); // Altera a classe
                } else {
                    button.text('Favoritar'); // Atualiza o texto do botão
                    button.removeClass('desfavoritar').addClass('favoritar'); // Altera a classe
                }
            },
            error: function(error) {
                console.log('Erro:', error); // Loga qualquer erro
            }
        });
    });
});
