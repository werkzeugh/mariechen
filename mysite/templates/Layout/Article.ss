<div class="main-row">

    <% if  getPageSections  %>

    <div class="nav-col">

        <div id="section-nav">
            <% loop  getPageSections %>
            <a href="#{$Slug}" <% if  First %> class="current-page" <% end_if %>>$Title</a>
            <% end_loop %>
        </div>
    </div>
    <% end_if %>


    <div class="main-col">

        <% loop getArticleElements %>

        $getHTML('maincontent')

        <% end_loop %>
        <% if  getPageSections  %>
        <div class="ce ce-toplink ce-text">
            <div class="ce-outer">
                <div class="ce-inner">
                    <a href="#top" title="back to top"><i class='fal fa-arrow-up'></i></a>
                </div>
            </div>
        </div>
        <% end_if %>
    </div>
</div>
<% if  getPageSections  %>
<script>
    jQuery(document).ready(function ($) {
        // Cache selectors
        var lastId,
            topMenu = $("#section-nav"),
            topMenuHeight = 200,
            // All list items
            menuItems = topMenu.find("a"),
            // Anchors corresponding to menu items
            scrollItems = menuItems.map(function () {
                var item = $($(this).attr("href"));
                if (item.length) {
                    return item;
                }
            });

        // Bind click handler to menu items
        // so we can get a fancy scroll animation
        menuItems.click(function (e) {
            var href = $(this).attr("href");
            var el = $(href)[0];
            el.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });

            e.preventDefault();
        });

        // Bind to scroll
        $(window).scroll(function () {
            // Get container scroll position
            var fromTop = $(this).scrollTop() + topMenuHeight;

            console.log('#log 5543 fromTop', fromTop);
            // Get id of current scroll item
            var cur = scrollItems.map(function () {
                if ($(this).offset().top < fromTop)
                    return this;
            });
            // Get the id of the current element
            cur = cur[cur.length - 1];
            var id = cur && cur.length ? cur[0].id : "";

            if (lastId !== id) {
                lastId = id;
                // Set/remove active class
                menuItems
                    .removeClass("current-page")
                    .filter("[href='#" + id + "']").addClass("current-page");
                changeURL(id)
            }
        });

        function changeURL(id) {
            window.history.pushState('', '', '#' + id);
        }

        var navcol = $('.nav-col')[0];
        var section_div = document.querySelector("#section-nav");

        function setFixedColumnWidth() {
            let parentWidth2 = navcol.offsetWidth;
            section_div.style.maxWidth = parentWidth2 + 'px';
        };

        setFixedColumnWidth();
        window.addEventListener('resize', setFixedColumnWidth);

    });
</script>
<% end_if %>
