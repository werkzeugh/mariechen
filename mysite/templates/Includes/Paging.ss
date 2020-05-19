<% if PaginationSource.MoreThanOnePage %>
<div class='paging group'>
  <div class='left'>
  <% if PaginationSource.PrevLink %>
    <a href="$PaginationSource.PrevLink">Zurück</a> | 
  <% end_if %>
 
  <% loop PaginationSource.Pages(10) %>
    <% if CurrentBool %>
      <strong>$PageNum</strong> 
    <% else %>

      <a href="$Link" title="Go to page $PageNum">$PageNum</a> 
    <% end_if %>
  <% end_loop %>
 
  <% if PaginationSource.NextLink %>
    | <a href="$PaginationSource.NextLink">Weiter</a>
  <% end_if %>
  </div>
  
</div>
<% end_if %>

