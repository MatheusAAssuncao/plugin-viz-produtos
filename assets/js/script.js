jQuery(document).ready(function($) {
    $('#load-more').on('click', function() {
        var page = $(this).data('page')
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'load_more_products',
                page: page
            },
            success: function(response) {
                $('.viz-widget-produtos').append(response)
                $('#load-more').data('page', page + 1)
            }
        })
    })
})