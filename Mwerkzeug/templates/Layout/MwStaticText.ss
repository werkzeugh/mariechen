<style>
    .scope_editbuttons {width:20px}
    a.EHP_saveitem {display:none}
    
    span.scope-default {color:#999;}
</style>

<h1>Static Texts</h1>
<div class='bootstrap'>   




    <div id='ehp'></div>


    <script type="text/javascript">

        // include ehp-widget ---------- BEGIN

        jQuery(document).ready(function($) {
            $('#ehp').EHP({
                'type':'listing',
                'baseurl':'{$CurrentURL}ehp',
                'listparams':{},
                'texts':{
                    'add_text':'none'
                },
                'pagesize':100,
                'columnChooser':true,
                'columns':
                    $getEHP.getJSONColumnDefinitions.RAW 
            });
        
            var emailID;
            $('#ehp a.resend-btn').live('click',function(e){
                e.preventDefault();
                $('#resendmodal').modal('show');
                emailID=$(this).closest('tr').attr('dbid');
                
            });
            
            $('a.modal_mailsend').click(function(e){
                e.preventDefault();
                $('#resendmodal').modal('hide');
                $('#sendingmodal').modal('show');
                
                var data={email:$('#modalemail').val(),id:emailID}
                url='{$CurrentURL}sendmail';
                $.post(url,data,function(result){
                    $('#sendingmodal').modal('hide');
                    
                });
            });
          
         });
        
        
        
        // include ehp-widget ---------- END

    </script>


  
      <div id='resendmodal' class="modal hide ">
          <div class="modal-header">
              <a class="close" data-dismiss="modal">×</a>
              <h3>send this mail to other address:</h3>
          </div>
          <div class="modal-body">
              <form class='form-horizontal'>
                  <label>e-mail-address:</label><input name='email' id='modalemail'>
              </form>
          </div>
          <div class="modal-footer">
              <a href="#" class="btn btn-primary modal_mailsend">Send</a>
          </div>
      </div>

      <div id='sendingmodal' class="modal hide ">
          <div class="modal-header">
              <a class="close" data-dismiss="modal">×</a>
              <h3>send this mail to other address:</h3>
          </div>
          <div class="modal-body">
             Sending mail <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">
          </div>
      </div>
    

    
</div>
      