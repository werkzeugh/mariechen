<% if YouTubeUrl %>

<div class="contentImg">
  $YouTubeHtml
  <% if PictureText  %><div class='typography space'>$PictureText</div><% end_if %>
</div>

<% else_if Picture %>
<div class="contentImg">
  $PictureLinkBegin
  <img src="$MainPicture.Link" title="$MainPicture.CopyrightText" width="$MainPicture.Width" height="$MainPicture.Height">
  $PictureLinkEnd
  <% if PictureText  %><div class='typography space'>$PictureText</div><% end_if %>
</div>   
<% end_if %>
