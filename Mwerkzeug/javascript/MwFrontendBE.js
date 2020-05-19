function reloadWindow()
{
  location.reload();
}

$(document).ready(function() {
  
  $("a.formsubmit").live('click',
  function() {

    if ($(this).attr('href').length > 2)
    $(this).closest('form').attr('action', $(this).attr('href'));

    $(this).closest('form').submit();
    return false;

  });

});
// end doc.ready



