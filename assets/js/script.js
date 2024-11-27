jQuery(document).ready(function($) {
    $("#load-more").on("click", function() {
        var button = $(this);
        var page = button.data("page");
        var postsPerPage = button.data("posts-per-page");
        console.log(ajax_object.ajax_url)
        console.log(ajax_object.viz_products_nonce)
        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "load_more_products",
                page: page + 1,
                posts_per_page: postsPerPage,
                nonce: ajax_object.viz_products_nonce
            },
            success: function(response) {
                if (response) {
                    $(".viz-widget-produtos").append(response);
                    button.data("page", page + 1);
                } else {
                    button.text("No more products").prop("disabled", true);
                }
            }
        });
    });
});