<!--
<ul>
 	<% loop Menu(1) %>
  		<li><a href="$Link" title="Go to the $Title.XML page" class="$LinkingMode"><span>$MenuTitle</span></a></li>
   	<% end_loop %>
 </ul>

 <li class="current_page_item"><a href="http://shabadeehoob.com">Blog</a></li>
-->
 
 <div class="utom_menu">

   <ul class="menu">
    <% loop Menu(2) %>
     <li class="page_item $LinkingMode"><a href="$Link">$MenuTitle</a></li>
    <% end_loop %>
      <li class="utom_menu_b"></li>
   </ul>

 </div>
 