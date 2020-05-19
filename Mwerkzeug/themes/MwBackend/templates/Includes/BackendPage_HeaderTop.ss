<div class="header-text">$ServerVar("HTTP_HOST")  - website Backend</div>
<% if CurrentMember %>
  <div class="header-text-right">
    welcome, <a href="/BE/profile">$CurrentMember.Email <i class='fa fa-cog'></i></a>
    <br><a href="/Security/logout">&raquo; log out</a>
  </div>
<% end_if %>       
