<div class='MwUser MwUserNarrow' >

    <h1>$Title</h1>

    <div class='bootstrap'>
    
        <% include MwUser_errorMessages %>

        <% if Content %>
        <div class='space'>$Content.RAW  </div>
        <% else %>
  
        <form method='POST' action='/User/lostpassword' class='space well span6' id='dataform'>

            <div class='MwUser_lostpassword space'>

                <div class='space'>
                    <% _t("lostpasswordText","Geben Sie hier ihre E-Mail Adresse ein, und wir senden Ihnen einen Link zur Passwort-Neuvergabe per E-Mail zu.") %>
                    <div class='space form-inline'>
                        <input type='text' name='fdata[SendPasswordLinkForUsername]' >&nbsp;
                        <button class="btn" type="submit"><i class="icon-ok"></i> OK</button>
                    </div>      
                </div>
                

            </div>

            <% if  showUsernameFinder %>
            <div class='space'><hr></div>

            <div class='MwUser_lostusername space'>
                <h2><% _t("lostusernameTitle","Sie haben Ihren Benutzernamen vergessen ?") %></h2>

                <form method='POST' action='/User/lostpassword' class='space'>
                    <% _t("lostusernameText","Geben Sie hier ihre E-Mail Adresse ein, und wir senden Ihnen Ihren Benutzernamen per E-Mail zu") %>

                    <div class='space form-inline'>
                        <input type='text' name='fdata[SendUsernameForEmail]'>&nbsp;
                        <button class="btn" type="submit"><i class="icon-ok"></i> OK</button>
                    </div>      

                </div>

            </form>

            <% end_if %>
                    

            <% end_if %>

        </div>
    </div>
  