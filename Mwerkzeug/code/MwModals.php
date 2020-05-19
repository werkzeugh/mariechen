<?php

use SilverStripe\Control\Controller;
use SilverStripe\View\Requirements;


class MwModals  {



    static public function includeRequirements()
    {
        
        
        
       MwBackendPageController::includeJquery();
       MwBackendPageController::includeMustache();
        
      //needs bootstrap
      //needs jquery 1.7  
      
      $html=Controller::curr()->renderWith('MwModals');
    
    $js=<<<JAVASCRIPT
    (function ( $ ) {

        var modal = function ( message, template, optionsViaArg ) {
            var dfd = $.Deferred();
            var template = $(template).html();
            var render = $(Mustache.to_html(template, {message: message}));
            var options={};
            
            options=$.extend({},optionsViaArg);

            var onSave = function ( e ) {
                    e.preventDefault();

                if(e.type === "keyup" && e.keyCode !== 13)
                    return;

                var input = render.find('input.value');
                var value = input.length > 0 && input.val();
            
               
                  
                if(options.onBeforeSave)
                  {
                     result=options.onBeforeSave.apply(render,[value]);
                     if(result!==true)
                             return;
                  }

                value ? dfd.resolve(value)
                      : dfd.resolve();
                render.modal('hide');

            };

            render
                .on('keyup.modal', '.value', onSave)
                .on('click.modal', '[data-action="save"]', onSave)
                .on('hidden', function () {
                    (dfd.state() == "pending") && dfd.reject();
                    $(this).remove();
                })
                .modal('show');
        
            if(options.onRender)
              {
                 options.onRender.apply(render);
              }        
                
            render.find('input.value').focus();

            return dfd.promise();
        };


        $.alert = function ( message,params ) {
            return modal(message, "#modal-alert-mjs",params);
        };

        $.confirm = function ( message,params ) {
            return modal(message, "#modal-confirm-mjs",params);
        };

        $.prompt = function ( message,params ) {
            return modal(message, "#modal-prompt-mjs",params);
        };

    })( window.jQuery );
JAVASCRIPT;
  
      Requirements::customScript($js);
      Requirements::insertHeadTags($html);
    
    
    
    }


}