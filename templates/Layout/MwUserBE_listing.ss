  
    <!-- include ehp-widget ---------- BEGIN -->  
    
    <h3>Users <% if CurrentGroup %> in Group  '$CurrentGroup.Title'<% end_if %></h3>
    
      <div id='ehp'></div>

      <script type="text/javascript">

      jQuery(document).ready(function($) {
          
              var mailfilter = {
                    'options':{
                        'SENT':'SENT' ,
                        'OPENED':'OPENED'
                   }
                };
                
            $('#ehp').EHP({
                'type':'listing',
                'use_bootstrap_css':1,
                'baseurl':'{$CurrentBaseURL}/ehp',
                'listparams':{'GroupID':'{$Url_ID}'},
                'defaultSortBy':{'LastEdited':'desc'},
                'texts':{
                  'add_text':<% if  Url_ID %>'add User to this Group'<% else %>
'none'<% end_if %>
                },
                'showCheckboxes':0,
                'columnChooser':true,
                'checkboxActions':{
                    // 'delete':true,
                    'send_invitation':{label:'send invitation'},
                    'send_reminder':{label:'send reminder'}
                },
                'columns':{
                    $EHP_getJSONColumnDefinitions.RAW
                }
                
              });
              
              
              $('a.loginas').live('click',function(e)
              {
                  e.preventDefault();
                  var dbid=$(this).closest('tr').attr('dbid');
                  top.location.href='/BE/User/loginAs/'+dbid;
              });
              
              
       });




      </script>
      <!-- include ehp-widget ---------- END -->  
    

