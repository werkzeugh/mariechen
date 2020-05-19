<div class="page-width-sm">

  <div class="pagearea-top">
    <div class="topcontainer">
      <h1><i class='fa fa-user fa-lg'></i> my profile</h1>
    </div>
  </div>
  
  <div class="topcontainer">

    <div>&nbsp;</div>
    <div>&nbsp;</div>
    <div>&nbsp;</div>


    <form id='dataform' class='formsection' method='POST' >

      $FormHelper.DefaultFields.RAW

      <% loop FormHelper.FormFields %>

      $HTML.RAW

      <% end_loop %>                    


    </form>
    <script>

      jQuery(document).ready(function($) {
        $FormHelper.validate.RAW; // jshint ignore:line
      });

    </script> 


  </div>
</div>
