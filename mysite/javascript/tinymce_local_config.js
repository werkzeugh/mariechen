
var local_tinymce_options_already_extended=0;

var handle_local_tinymce_options=function(options,element) {
  
  if(!local_tinymce_options_already_extended)
  {
    options.style_formats.push(
    {
      title: 'Hervorhebung',
      block: 'p',
      classes: 'typopgraphy-special'
    });
    
    local_tinymce_options_already_extended=1;
    
  }
    
  return options;  
};




