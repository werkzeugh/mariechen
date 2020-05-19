  
<style>



</style>


<h1>$Title</h1>

<% if step == 1 %>

<div class='niceform profilepage'>
  
<p class='space'><% _t('profilehead','Hier können Sie Ihre Profildaten ändern:') %></p>

<% include MwUser_errorMessages %>


  <form id='dataform' method='POST'>
    <input type="hidden" name="NextStep" value="finish" id="Input_NextStep">


    <div class='formgroup'>
      <div class='group'>
        <div class='column' style='width:200px'>$FormField(Sex).HTML.RAW</div>
        <div class='column' style='width:200px'>$FormField(PreTitle).HTML.RAW</div>
      </div>

      <div class='group'>
        <div class='column' style='width:200px'>$FormField(FirstName).HTML.RAW</div>
        <div class='column' style='width:200px'>$FormField(Surname).HTML.RAW</div>
      </div>

    </div>

    <div class='formgroup'>
      <div class='group'>
          <div class='column' style='width:200px'>$FormField(FonBusiness).HTML.RAW</div>
          <div class='column' style='width:200px'>$FormField(FonMobile).HTML.RAW</div>      
      </div>
      <div class='group'>
        <div class='column' style='width:200px'>$FormField(Email).HTML.RAW</div>
        <div class='column' style='width:200px'>$FormField(Passwordchangelink).HTML.RAW</div>
      </div>
    </div>

    <div class='formgroup'>
      <h2><% _t('Anschrift','Anschrift:') %></h2>
      <div class='group'>
        <div class='column' style='width:425px'>$FormField(Street).HTML.RAW</div>
      </div>
      <div class='group hideOnAdd'>
        <div class='column' style='width:50px'>$FormField(Zip).HTML.RAW</div>
        <div class='column' style='width:150px'>$FormField(City).HTML.RAW</div>
        <div class='column' style='width:200px'>$FormField(CountryISO).HTML.RAW</div>
      </div>
    </div>


    <div class='space'> <a href='#' class='nicebutton submit'>ABSCHICKEN</a></div>


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

<div class='space'>
  Ihr Profil wurde gespeichert.
</div>  


<% end_if %>
