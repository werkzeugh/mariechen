<div id="Sidebar" class="secondary typography">

<% if Children %>

  <h2><a href="$Link">$Title</a></h2>
  <div class="featured">
    <ul id="Menu2">
      <% loop Children %>
      <li><a href="$Link" title="Go to the $Title.XML page" class="$LinkingMode levela"><span>$MenuTitle</span></a>
      <% end_loop %>
    </ul>
  </div>

<% else %>


  <% with Parent %>
  <h2><a href="$Link">$Title</a></h2>
  <div class="featured">
    <ul id="Menu2">
      <% loop Children %>
      <li><a href="$Link" title="Go to the $Title.XML page" class="$LinkingMode levela"><span>$MenuTitle</span></a>
      <% end_loop %>
    </ul>
  </div>
  <% end_with %>

<% end_if %>

</div>

