<h1>Registration for $SiteName $step</h1>

please customize ! this form is just a placeholder

<% include MwUser_errorMessages %>


<% if step == 1 %>

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
</form>

<a href='#' class='button submit'><span class='tinyicon ui-icon-check'></span>GO</a>

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
        $getJSValidationRules.RAW
      },
      messages: {
        $getJSValidationMessages.RAW
      }
    });

// Standard jQuery footer
})
</script>

<% end_if %>

<% if step == finish %>

<div class='space'>
     user written successfully
</div>

<% end_if %>




