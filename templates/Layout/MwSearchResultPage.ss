


<h1>$Title</h1>

<form class='MwSearchResultForm'>

  <input type='text' name='q' value='$CurrentKeyword'>

</form>

<div class='MwSearchResultPages'>
<% if FoundPages %>

  $getPaging(FoundPages)
	<ul class='' >
		<% loop FoundPages %>
			<li>
				<a href='$Link'>$Title</a>
				<p>$Excerpt</p>
			</li>
		<% end_loop %>
	</ul>
  $getPaging(FoundPages)

<% else %>
<% if CurrentKeyword %>
	Ihre Suche lieferte leider keine Ergebnisse.
<% end_if %>
    
<% end_if %></div>
