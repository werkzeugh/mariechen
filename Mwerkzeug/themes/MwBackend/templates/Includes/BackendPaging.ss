<% if PaginationSource.MoreThanOnePage %>
<div class='paging group'>
    <div>
      <% if PaginationSource.PrevLink %>
        <a href="$PagingLinkPrefix$PaginationSource.PrevLink">&lt;&lt; Zurück</a> | 
      <% end_if %>
     
      <% loop PaginationSource.Pages %>
        <% if CurrentBool %>
          <strong>$PageNum</strong> 
        <% else %>
          <a href="$Top.PagingLinkPrefix$Link" title="Go to page $PageNum">$PageNum</a> 
        <% end_if %>
      <% end_loop %>
     
      <% if PaginationSource.NextLink %>
        | <a href="$PagingLinkPrefix$PaginationSource.NextLink">Weiter &gt;&gt;</a>
      <% end_if %>
    </div>
    <div class='middle'>
  Treffer pro Seite: $PageSizeSelector.RAW
  </div>
  
  
</div>
<% end_if %>

