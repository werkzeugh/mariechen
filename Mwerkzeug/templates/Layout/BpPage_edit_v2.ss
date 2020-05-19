<script>
  $(document).ready(function() {
    var PageTree;

    var inIframe = window !== window.top;

    <% if RequestVar("keep_unframed") || RequestVar("hot") || RequestVar("flush") %>inIframe=true;<% end_if %> // jshint ignore:line

    if(inIframe)
    {
      parent.location.hash = "$UrlHash";
    }
    else
    {
      window.open('$Redirect2FrameUrl','_self');
    }

    PageTree=$('#leftframe',parent.document).contents().find('#jsTree');
    if(window.parent!==window) {
      window.parent.setCurrentPageID('{$record.ID}');
    }
  });
</script>


<div class='page-edit page-inframe'>
  <form id='dataform' method='POST' class='$record.ClassName'>
  <div class="pagearea-top">
    <h1>

          <i class='$NodeData.icon'></i>

          <% if  $RealMenuTitle %><div class='title-short'>$RealMenuTitle<small class="tinylabel">= <% _t('backend.labels.page_MenuTitle_short') %></small></div>
            <div class="title-long">$Title</div>
          <% else %>
            <div class="title-short">$Title</div>
          <% end_if %>
          <div class='page-url'>Path: $linkBaseUrl<b>$record.URLSegment</b></div>
          <div class='page-class'><i class='fa fa-tag'></i> $record.ClassName - created: $record.Created - <span id='historylink'>modified</span>: $record.LastEdited - ID:$record.ID - 

          <em><% if $record.Hidden  %>
              <% _t('backend.labels.page_Hidden_1') %>
            <% else %>
              <% _t('backend.labels.page_Hidden_0') %>
            <% end_if %></em>

          <% if  $record.HistoryHtml %>
            <div id="page-history">$HistoryHtml</div>
            <script>
            \$('#historylink').on('click',function(){
              \$('#page-history').slideToggle();
            }).css({'text-decoration':'underline'});
            </script>
         <% end_if %>
          </div>

    </h1>

    <div class='right-top'>


      <% if  isAllowed('modifyPage') %>
          <button class='btn btn-primary' type='submit' id='main_savelink' name='main_savelink'><i class='fa fa-check'></i> <% _t('backend.labels.save_page') %></button>
      <% else %>

      <button disabled class='btn btn-danger'><i class='fa fa-lock fa-lg'></i> <% _t('backend.labels.page_locked') %></button>
      <% end_if %>


      <% if  isAllowed('previewPage') %>
       <a href='$record.PreviewLink' target='_self' class='btn btn-default ' ><i class='fa fa-arrow-right'></i> <% _t('backend.labels.preview_page') %></a>
      <% end_if %>


      <% if  isAllowed('useActionMenuOnPage') %>

      <span id="actionmenuApp">
       <div class="btn-group" dropdown>
        <button id="single-button" type="button" class="btn btn-primary dropdown-toggle" disabled=1 ng-disabled="disabled">
         <% _t('backend.labels.pageactions_button') %><span class="loading" ng-show="loading"> <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"> </span><span class="caret"></span>
       </button>
       <ul actionmenu-items page-id="$record.ID" app="app">
       </ul>
     </div>
     <% end_if %>


      <!--   <div class="btn" dropdown>
          <button type="button" class="btn btn-primary" dropdown-toggle disabled=1 ng-disabled="disabled">
          <% _t('backend.labels.pageactions_button') %> <span class="loading" ng-show="loading"><img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"></span><span class="caret"></span>
          </button>
          <ul class="dropdown-menu" role="menu" aria-labelledby="btn-append-to-body">
            <li role="menuitem"><a href="#">Action</a></li>
            <li role="menuitem"><a href="#">Another action</a></li>
            <li role="menuitem"><a href="#">Something else here</a></li>
            <li class="divider"></li>
            <li role="menuitem"><a href="#">Separated link</a></li>
          </ul>

        </div> -->
      </span>

    </div>

    <% include TabNavigation %>
  </div>
  
  <% if NoDataForm %>
    </form>
  <% end_if %>

 <div class="pagearea-main">
    <div id='edit_errorContainer' class='errorContainer'>
     Please fix the <span class='warning'>highlighted Fields.</span>
     <div id='edit_errorLabelContainer' class='errorLabelContainer'>&nbsp;</div> 
   </div>

     <input type='hidden' name='ReturnURL' value='$ReturnURL' />
     <input type='hidden' name='NextAction' value='' />
     <input  type="hidden" name="NextURL" value="$ServerVar(REQUEST_URI)" /><!-- for sorting functionality -->

  

     <% if  CustomTabHTML  %>
     $CustomTabHTML.RAW.RAW
     <% else %>
       &nbsp;
       <% if AllFormFields %>
       <div class='formsection form form-horizontal'>
         <table class='ftable'>
           $AllFormFields.RAW
         </table>
       </div>
       <% end_if %>

     <% end_if %>

     <div class='formsection actions'>

       <% if  isAllowed('modifyPage') %>
         <button class='btn btn-primary' id='main_savelink' type='submit'><i class='fa fa-check'></i> <% _t('backend.labels.save_page') %></button>
       <% end_if %>

     </div>


 </div>
</form>

   <div id='preview'>
     <iframe id='previewframe' name='previewframe'></iframe>
   </div>

</div>
<script>
  jQuery(document).ready(function($) {

    $(".do-confirm").on('click', function() {
      if (confirm('<% _t('backend.labels.confirm_pagedeletion') %>')) {
        window.location=$(this).data('href');
      }
    });

    <% if RequestVar("keep_unframed") %>

    <% else %>

//    $('#previewframe').attr('src','$record.PreviewLink');

    <% end_if %>

  }); 
</script>
<script src="/Mwerkzeug/bower_components/requirejs/require.min.js" data-main="/Mwerkzeug/ng/actionmenu/js/main.js"></script>
