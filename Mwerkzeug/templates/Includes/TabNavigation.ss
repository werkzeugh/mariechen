<% if TabItems.Count == 1 %>

<% else %>

<ul class="tabstrip">
<% loop TabItems %>

 <li class="<% if First %>first <% end_if %><% if Last %>last <% end_if %><% if Current %>current<% end_if %>">
   <% if Link %>
     <a href="$Link" class="submit">$Title</a>
   <% else %>
     <em>$Title</em>
   <% end_if %>
   </li>
 
<% end_loop %>
</ul>
<% end_if %>


