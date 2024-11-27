jQuery(document).ready(function($) {
    $("#load-more-products").on("click", function() {
        var button = $(this)
        var page = button.data("page")
        var postsPerPage = button.data("posts-per-page")

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "load_more_products",
                page: page + 1,
                posts_per_page: postsPerPage,
                nonce: ajax_object.nonce
            },
            success: function(response) {
                if (response) {
                    $(".viz-widget-produtos").append(response)
                    button.data("page", page + 1)
                } else {
                    button.text("No more products").prop("disabled", true)
                }
            },
            error: function(response) {
                response = JSON.parse(response)
                console.log("Error: " + response)
            }
        })
    })
})