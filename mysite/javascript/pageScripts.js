var ready = function (callback) {
        if (document.readyState != "loading") {
                callback();
        } else {
                document.addEventListener("DOMContentLoaded", callback);
        }
}


function setCartCount(count) {
        if (count !== null) {
                var elements = document.querySelectorAll(".cart-count");
                Array.prototype.forEach.call(elements, function (el, i) {
                        el.textContent = count;

                });
        }
}

function getCartCount(callback) {
        var url = "/ex/cartapi/get_cart_count"
        jQuery.ajax({
                        url: url,
                        dataType: "json"
                })
                .done(function (data) {
                        callback.apply(this, [data.payload]);
                });
}

function updateCartCount() {
        getCartCount(function (count) {
                setCartCount(count);
        });
}

function showCartAdd() {
        jQuery("#cart-infobox").fadeIn('slow');
        setTimeout(function () {
                jQuery("#cart-infobox").fadeOut('slow');
        }, 4000);

}

function initialCartCount() {
        getCartCount(function (count) {
                setCartCount(count);
                setTimeout(function () {
                        checkCartCount(count);
                }, 200);
                setTimeout(function () {
                        checkCartCount(count);
                }, 1000);
        });
}

function checkCartCount(count) {
        var countEl = document.querySelector("#cart-count");
        if (countEl && countEl.textContent == '') {
                setCartCount(count);
        }
}


ready(function () {

        initialCartCount();



        var setNav = function (event) {
                event.preventDefault();
                var c = $(this).parent().children('.active').removeClass('active').removeClass(
                        'current-content').addClass('not-active');
                var url = $(this).attr('href');
                var key = $(this).data('key');
                $(this).addClass('active').addClass('just-activated').removeClass('not-active');
                $(this).one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
                        function (event) {
                                window.location = url;
                                $('.main-content').removeClass('visible');

                                $('body').addClass('page-' + key).removeClass('page-$URLSegment');
                        });

        };
        var toggleMenu = function () {
                $('#pagenav').slideToggle();
        }

        var toggleCurrency = function () {
                $('#currencychooser').slideToggle();
        }


        $('>a', '#pagenav').on('click', setNav);

        $('.main-content').addClass('visible');

        $('#burgerlink').on('click', toggleMenu);
        $('#currencylink').on('click', toggleCurrency);
        // $('#currencychoose').on('click', setCurrency);
});
