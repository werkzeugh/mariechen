<h1>$Title</h1>

<div class='bootstrap'>
<% include MwUser_errorMessages %>

$Content.RAW  

<% if ShowForm %>
<form id="dataform" action="/User/login" method="post" autocomplete="off">

  <input class="hidden"  name="BackURL" value="$BackURL" type="hidden">

  <div id='MwUserLoginBox'>

    <div class='MwUserLoginBoxField'> 
      <label><% _t('Username','Username') %></label>
      <input type='text' name='fdata[Username]' id='inputUsername' >
    </div>

    <div class='MwUserLoginBoxField'> 
      <label><% _t('Password','Password') %></label>
      <input name='fdata[Password]' type='password'>
    </div>
    
    
    <div class='MwUserLoginBoxField'> 
      <label>&nbsp;</label>
      <input type='checkbox' name='fdata[RememberMe]' value='1'>
      <% _t('RememberMe','auf diesem Computer eingeloggt bleiben') %>
    </div>
    
    <div class='MwUserLoginBoxField space'> 
           <button class="btn btn-primary" type="submit"><i class="icon-white icon-ok"></i> OK</button>
    </div>
    

  </div>

  <div class='space'><a href='/User/lostpassword'>&raquo; <% _t('LostPassword','Passwort vergessen ?') %> </a></div>

  <div class='helpbubble'>
    <% _t('LOGININFO',' ') %>
  </div>

</form>
<% end_if %>
</div>

<script type="text/javascript" charset="utf-8">
  
 $(document).ready(function() {

   $('#inputUsername').focus();

  });
  
</script>
