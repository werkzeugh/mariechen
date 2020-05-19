<div id="header" class="group">
    <div id="header-inner">
        <div id="header1" class='header-top'>
            $HeaderTopHtml        
        </div>
        <div class="header-bottom">
        <% if UserCanEdit %>
            
            <div id="nav" class="group">
                <ul class="group">
                    <% loop MainNavItems %>
                    <li>
                        <a href="$Link" class="$LinkingMode">$Title</a>
                    </li><% end_loop %>
                </ul>
            </div><% if SubNavItems %>
            <div id="subnav" class="group">
                <ul class="group">
                    <% loop SubNavItems %>
                    <li>
                        <a href="$Link" class="$LinkingMode">$Title</a>
                    </li><% end_loop %>
                </ul>
            </div><% end_if %>
            <% end_if %>
            </div>

    </div>
</div>
