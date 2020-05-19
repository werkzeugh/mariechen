<div class='MwUser MwUserNarrow' >

  <h1>$Title</h1>

  <% include MwUser_errorMessages %>

  <% if Content %>
   <div class='space'>$Content.RAW  </div>

   <% include MwUser_buttons %>
   
  <% else_if ShowForm %>
  
  
  <form method='POST' action='$CurrentURL' class='space bootstrap' id='dataform'>

    <div class='MwUser_resetpassword space'>
      <div class='space'>
        <% _t("lostpasswordText","Geben Sie hier Bitte ihr neues Passwort ein:") %>
        <div class='space'>
          <label><% _t("username","Benutzername") %></label><strong>$User.UsernameOrEmail</strong>
        </div>
        <div class='space'>
          <label><% _t("password","Passwort") %></label><input type='password' name='fdata[Password1]'>
          <label><% _t("password_again","Passwort (nochmal)") %></label><input type='password' name='fdata[Password2]'>
        </div>      
        <button class="btn btn-primary" type="submit"><i class="icon-white icon-ok"></i> OK</button>
      </div>

    </div>
    </form>
<% end_if %>

  </div>
