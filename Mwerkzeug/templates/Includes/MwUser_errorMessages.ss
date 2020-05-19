<% if errorMessages %>

<div class='bootstrap mwuser-errormsg'>
     <% loop errorMessages %>
       <div class="alert alert-error alert-danger">$msg</div>
     <% end_loop %>
 </div>
<% end_if %>
