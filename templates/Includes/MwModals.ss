<script id="modal-alert-mjs" type="text/template">
       // Mustache template, use {% raw %}{% endraw %} if you render with jinja2
       <div class="modal" id="modal-alert">
           <div class="modal-header">
               <a class="close" data-dismiss="modal">×</a>
               <h3>Modal header</h3>
           </div>

           <div class="modal-body">
               <p>{{{ message }}}</p>
           </div>
           <div class="modal-footer">
               <a class="btn btn-primary" data-action="save"><i class='icon-ok icon-white'></i> OK </a>
           </div>
       </div>
</script>
             
<script id="modal-confirm-mjs" type="text/template">
       // Mustache template, use {% raw %}{% endraw %} if you render with jinja2
       <div class="modal" id="modal-confirm">
           <div class="modal-header">
               <a class="close" data-dismiss="modal">×</a>
               <h3>Modal header</h3>
           </div>

           <div class="modal-body">
               <p>{{{ message }}}</p>
           </div>

           <div class="modal-footer">
               <a class="btn" data-dismiss="modal"><i class='icon-remove '></i> Close </a>
               <a class="btn btn-primary" data-action="save"><i class='icon-ok icon-white'></i> OK </a>
           </div>
       </div>
   </script>

   <script id="modal-prompt-mjs" type="text/template">
       <div class="modal" id="modal-prompt">
           <div class="modal-header">
               <a class="close" data-dismiss="modal">×</a>
               <h3>{{{ message }}}</h3>
           </div>

           <div class="modal-body">
               <p>
                   <input type="text" style="width: 518px" class="value" />
               </p>
           </div>

           <div class="modal-footer">
               <a class="btn" data-dismiss="modal"><i class='icon-remove '></i> Close </a>
               <a class="btn btn-primary" data-action="save"><i class='icon-ok icon-white'></i> OK </a>
           </div>
       </div>
   </script>