<h1>Neuanmeldung zum Kennenlern-Einkauf</h1>

<div class='bootstrap'>
    
    
    
    
    <% include MwUser_errorMessages %>



<% if step == 1 %>


<div class='space well '>
        
    Hier können Sie sich für den doppelstock-Shop anmelden.
    <div>&nbsp;</div>
        
    Im Anschluss an die Registrierung erhalten Sie ihren 
    persönlichen Gutschein-Code für Ihren Kennenlern-Einkauf.
        
</div>


<form id='dataform' method='POST'>
  <input type="hidden" name="NextStep" value="finish" id="Input_NextStep">
  <div class='formular'>
      <div class='group'>
        <ul>
          <% loop FormFields  %>
            <li>$HTML.RAW</li>
          <% end_loop %>
        </ul>
      </div>

      <button class="btn btn-info" type="submit"><i class="icon-white icon-ok"></i> OK</button>

</form>


</div>


<script type="text/javascript" charset="utf-8">
// Standard jQuery header
  $(document).ready(function() {

    $.extend($.validator.messages, {
        required: "Dieses Feld ist ein Pflichtfeld.",
      equalTo: "Bitte geben Sie dasselbe Passwort erneut ein.",
        email: "Geben Sie bitte eine gültige E-Mail Adresse ein.",
      });
    
    $.validator.addMethod("password", function( value, element ) {
            var result = this.optional(element) || value.length >= 6 && /[a-z]/i.test(value);
            if (!result) {
                element.value = "";
                var validator = this;
                setTimeout(function() {
                    validator.blockFocusCleanup = true;
                    element.focus();
                    validator.blockFocusCleanup = false;
                }, 1);
            }
            return result;
        }, "Ihr Passwort muss mindestens 6 Zeichen haben.");


        $.validator.addMethod("username", function( value, element ) {
                var result = this.optional(element) || value.length >= 5 && /^[a-z0-9A-Z._@-]+$/.test(value) 
                if (!result) {
                    var validator = this;
                    setTimeout(function() {
                        validator.blockFocusCleanup = true;
                        element.focus();
                        validator.blockFocusCleanup = false;
                    }, 1);
                }
                return result;
            }, "Ihre Benutzername muss mindestens 5 Zeichen lang sein und darf nur folgende Zeichen beinhalten: a-z, 0-9, -, @, _, . ");


    $("#dataform").validate({
      errorLabelContainer: "#edit_errorLabelContainer",
      errorContainer: "#edit_errorContainer",
      errorClass: "warning",
      highlight: function(element, errorClass) {
        $(element).siblings('label').addClass('highlight');
      },
      unhighlight: function(element, errorClass) {
        $(element).siblings('label').removeClass('highlight');
      },        
      onkeyup: false, 
      rules: {
        $getJSValidationRules
      },
      messages: {
        $getJSValidationMessages
      }
    });

// Standard jQuery footer
})
</script>

<% end_if %>

<% if step == finish %>

<div class='space well'>

     Ihr Account wurde angelegt.
     <div>&nbsp;</div>
     <strong>Bevor Sie sich im Shop anmelden können, müssen Sie zuerst Ihren Account aktivieren:</strong>
     <div>&nbsp;</div>
     Bitte klicken Sie dazu den Link in der e-mail die wir Ihnen soeben an Ihre e-Mail Adresse gesendet haben.
     
     
</div>

<% end_if %></div>
