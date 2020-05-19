<% if ListMode == icons %>

<div class="_thumbnail" title="$CopyrightText" <% if isSvg %>
  style="width:100%;height:80px;background-image:url('$Image.Link?$Image.LastEdited');background-position:center;background-size:contain;background-repeat:no-repeat"
  <% else %>
  style="width:{$Image.CMSThumbnail.getWidth}px;height:{$Image.CMSThumbnail.getHeight}px;background-image:url('$Image.CMSThumbnail.Link?$Image.LastEdited')"
  <% end_if %>>
  
  <% if Copyright %>
  <div class='_copyright _hover'>$ShortCopyrightText</div>
  <% end_if %>
</div>

<div class='_filename' title='$Title'>$ShortTitle</div>


<% else %>

<div style='position:relative'>
  <div class='dragicon jstree-draggable'></div>
  <div class='group line'>
    <span class='itemtitle'>$Title</span>
    <span class='copyright'>$getShortCopyrightText(100)</span>
    <span class='_thumbnail'>&nbsp;</span>
  </div>
</div>

<% end_if %>
