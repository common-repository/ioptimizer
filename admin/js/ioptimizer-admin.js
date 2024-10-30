(function ($) {
    var count = 0;
    var tokens;
    'use strict';

    $(function () {
        console.log(ioptimizer_globals);
        wp.ajax.post('get_tokens', {_ajax_nonce: ioptimizer_globals.get_tokens_nounce}).done(function (response) {
            $('.tokens-no').html(response.tokenNo);
            tokens = parseInt(response.tokenNo);
        });
        getImages();
    });
    $(document).on('change', '.ioptcheck', function () {
        count = 0;
        $('.ioptcheck').each(function (elem) {
            if (!$(this).is(':disabled') && $(this).prop("checked")) {
                count = count + 1;
            }
        });
        displayTokens();
    });
    $(document).on('click', '.bulk-process', function (event) {
        var posts = [];
        $('.ioptcheck').each(function (elem) {
            if (!$(this).is(':disabled') && $(this).prop("checked")) {
                posts.push($(this).data('image_id'))
            }
        });
        if (posts.length > 0 && tokens >= count) {
            $('.select-all').attr('disabled', 'disabled');
            $('.bulk-process').attr('disabled', 'disabled');
            var data = {
                action: 'bulk_process',
                posts: posts,
                _ajax_nonce: ioptimizer_globals.bulk_process_nounce
            };

            wp.ajax.post("bulk_process", data)
                .done(function (response) {
                    $('.select-all').removeAttr("disabled");
                    $('.bulk-process').removeAttr("disabled");
                    $('.images-list >tbody').empty();
                    getImages();
                }).fail(function (error) {
                alert(error);
            });
        }
    });
    $(document).on('click', '.select-all', function (event) {
        count = 0;
        $('.ioptcheck').each(function (elem) {
            if (!$(this).is(':disabled') && !$(this).prop("checked")) {
                $(this).prop("checked", true);
                count = count + 1;
            }
        });
        displayTokens();
        $(this).attr('value', 'Deselect all');
        $(this).removeClass('select-all').addClass('deselect-all');
    });
    $(document).on('click', '.deselect-all', function (event) {
        $('.ioptcheck').each(function (elem) {
            if (!$(this).is(':disabled')) {
                $(this).prop("checked", false);
            }
        });
        count = 0;
        displayTokens();
        $(this).attr('value', 'Select all');
        $(this).removeClass('deselect-all').addClass('select-all');
    });

    function getImages() {
        var count = $('.images-list >tbody >tr').length;
        wp.ajax.post('get_image', {existing_rows: count, _ajax_nonce: ioptimizer_globals.get_image_nounce}).done(function (response) {
            if (response.continue === true) {
                $('.images-list >tbody').append(response.html);
                getImages();
            } else {
                $('.select-all').removeAttr("disabled");
                $('.bulk-process').removeAttr("disabled");
            }
        }).fail(function (error) {
            alert(error);
        });
    }

    function displayTokens() {
        if (count != 0) {
            $('.tokens-needed').html("<p>Credits required:" + count + "</p>");
        } else {
            $('.tokens-needed').empty();
        }
    }
})(jQuery);

window.addEventListener("load", function () {

    // store tabs variables
    var tabs = document.querySelectorAll("ul.nav-tabs > li");

    for (i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener("click", switchTab);
    }

    function switchTab(event) {
        event.preventDefault();

        document.querySelector("ul.nav-tabs li.active").classList.remove("active");
        document.querySelector(".tab-pane.active").classList.remove("active");

        var clickedTab = event.currentTarget;
        var anchor = event.target;
        var activePaneID = anchor.getAttribute("href");

        clickedTab.classList.add("active");
        document.querySelector(activePaneID).classList.add("active");

    }

});
