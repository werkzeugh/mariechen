<style>
    .pagelist li {margin:5px 0px}
    .pagelist li a.current,
    .pagelist li a:hover {font-weight:bold}
    #dataform {width:600px;}
    
    <% if   UseFrames %>
      #editcontent {margin-top:10px}
    <% end_if %>
    
    
</style>
<table id='editcontent'>
    <tr valign='top'><td style='position:relative' >
        
      <% if UseFrames %>
          <script>
          $(document).ready(function() {
              var PageTree;
              <% if   UseFrames %>

                  var in_iframe = window !== window.top;
                  
                  <% if RequestVar("keep_unframed") || RequestVar("flush") %>in_iframe=true;<% end_if %>
                  
                  if(in_iframe)
                      {
                          parent.location.hash = "$UrlHash";
                      }
                      else
                      {
                          //reload this page within proper frame:
                          window.open('$Redirect2FrameUrl','_self');
                      }
                        
                  PageTree=$('#leftframe',parent.document).contents().find('#jsTree');
              <% else %>
                  PageTree=$('#jsTree');
              <% end_if %>
              if(window.parent!=window)
              {
                  window.parent.setCurrentPageID('{$record.ID}');
              }
          });
      </script>

      <% else %>
      
      <div class='group'>
               <% include BpPage_Pagetree %>
      </div>       
      <% end_if %>
      
    </td>    
    <td>
      <div style='position:relative' class="editcontent-wrapper">

      <div style='width:800px;position:relative'>
       
        <div style='color:#CCC;font-size:9px;position:absolute;top:-10px;right:0px;width:300px;text-align:right'>$ClassName

        <div class='righttop'>
          <a href='#' class='button save submit' id='main_savelink'><span class="tinyicon ui-icon-check"></span><% _t('SavePage','save page') %></a>
          <a href='$record.Link' target='_blank' class='button' ><span class="tinyicon ui-icon-search"></span><% _t('PreviewInNewWindow','Preview in new Window') %></a>
                  <div class='changedates'>($Created / $LastEdited)</div></div>

        </div>
      </div>

    <h1>$Title</h1>

    <% include TabNavigation %>

    <div id='edit_errorContainer' class='errorContainer'>
        Please fix the <span class='warning'>highlighted Fields.</span>
        <div id='edit_errorLabelContainer' class='errorLabelContainer'>&nbsp;</div> 
    </div>
    

    <form id='dataform' method='POST' class='$record.ClassName'>
        <input type='hidden' name='ReturnURL' value='$ReturnURL' />
        <input type='hidden' name='NextAction' value='' />
        <input  type="hidden" name="NextURL" value="$ServerVar(REQUEST_URI)" /><!-- for sorting functionality -->

        <% if  CustomTabHTML  %>
          $CustomTabHTML.RAW.RAW
        <% else %>
        &nbsp;
          <% if AllFormFields %>
            <div class='formsection'>
                <table class='ftable'>
                  $AllFormFields.RAW
                </table>
            </div>
          <% end_if %>

         <% end_if %>
        


        <div class='actions'>

            <a href='#' class='button submit' id='main_savelink'><span class='tinyicon ui-icon-check'></span><% _t('SavePage','save page') %></a>
            <a href='/BE/Pages/delete/$record.ID' class='button delete confirm'><span class='tinyicon ui-icon-trash'></span><% _t('DeletePage','delete Page') %></a>
           
            <div class='clear'></div>

        </div>

    </form>

    <div id='preview'>
        <h3 style='margin-top:10px'>Preview</h3>
        <iframe id='previewframe' name='previewframe'></iframe>
    </div>

    <script type="text/javascript" charset="utf-8">

        // urlsegmentcheck ---------- BEGIN

        var input_Title_stored;

        input_Title_stored=$('#input_Title').val();

        $('#dataform').submit(function(value, element) {
            if(input_Title_stored==$('#input_Title').val())
            {
                return true; //continue submit
            }
            else
            {
               // console.log('field has changed');
            }
            if($('#input_Title').length>0)
            {
                $('#input_Title_URLSegmentCheck').remove();
                $('#input_Title').after("<span id='input_Title_URLSegmentCheck'></span>");
                var params={
                    'MwLink':'$record.MwLink',
                    'Title':$('#input_Title').val()
                    };

                if($('#input_URLSegment').length>0)
                {
                    params.URLSegment=$('#input_URLSegment').val();
                }
                $('#input_Title_URLSegmentCheck').load('/BE/Pages/URLSegmentCheck/',params);

            }
            return false;

        });

        // urlsegmentcheck ---------- END
        $(document).ready(function() {
       
        $("#dataform").validate({
        errorLabelContainer: "#edit_errorLabelContainer",
        errorContainer: "#edit_errorContainer",
        errorClass: "warning",
        highlight: function(element, errorClass) {
        $(element).siblings('label').addClass(errorClass);
        $(element).closest('tr').find("label").addClass(errorClass);
        },
        unhighlight: function(element, errorClass) {
        $(element).siblings('label').removeClass(errorClass);
        $(element).closest('tr').find("label").removeClass(errorClass);
        },   
        rules: {
        $getJSValidationRules.RAW
        },
        messages: {
        $getJSValidationMessages.RAW
        }
        });
        
        <% if RequestVar("keep_unframed") %>

        <% else %>

          $('#previewframe').attr('src','$record.PreviewLink');
          
        <% end_if %>


        });

    </script>
</div>
</td></tr>
</table>

<div style='color:#ccc'>$record.ClassName</div>


