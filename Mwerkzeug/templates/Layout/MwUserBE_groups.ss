    <!-- include ehp-widget ---------- BEGIN -->  
    
    <h3>Usergroups</h3>
    
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
                'baseurl':'{$CurrentBaseURL}/groupehp',
                'dragdrop_sort':1,
                'texts':{
                  'add_text':'add Group'
                },
                'showCheckboxes':0,
                'checkboxActions':{
                    // 'delete':true,
                    'send_invitation':{label:'send invitation'},
                    'send_reminder':{label:'send reminder'}
                },
                'columns':{
                    'Title': {label:'auto'},
                    'Code': {label:'auto'},
                    'Description': {label:'auto'},
                }
                
              });
       });

      </script>
      <!-- include ehp-widget ---------- END -->  
    

