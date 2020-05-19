<% if Buttons %>

<div class='bootstrap'>
    <div class='MwUser_Buttons btn-group'> 
        <% loop Buttons %>
        <a class="btn btn-small <% if Primary %>btn-primary<% end_if %>" href="$Link"><i class="<% if Primary %>icon-white<% end_if %> $IconClass"></i> $Title </a>
        <% end_loop %>
    </div>
</div><% end_if %>

