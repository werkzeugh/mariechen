if (typeof console === "undefined") {
    window.console = {
        log: function() {},
        dir: function() {},
        dirxml: function() {}
    };
}

function reloadWindow()
 {
    location.reload();
}

var setup_datepickers,globalDataformSubmitClickHandler;

$(document).ready(function() {



    if (jQuery().datepicker)
     {
        setup_datepickers=function()
        {
          if ($('.datepicker').length > 0)
          {
            $('.datepicker').datepicker({
              changeMonth: true,
              changeYear: true,
              showButtonPanel: true,
              dateFormat: "yy-mm-dd",
              showOn: "button",
              yearRange: '-2:+4',
              buttonImage: "/Mwerkzeug/images/calendar.gif",
              buttonImageOnly: true
            }).attr('title', 'format: YYYY-MM-DD').width(90);
          }

          if ($('.datetimepicker').length > 0)
          {
            $('.datetimepicker').datepicker({
              changeMonth: true,
              changeYear: true,
              showButtonPanel: true,
              showOn: "button",
              yearRange: '-1:+4',
              //minDate:'0',
              dateFormat: 'dd.mm.yy 00:00',
              buttonImage: "/Mwerkzeug/images/calendar.gif",
              buttonImageOnly: true
            }).width(120);

          }
        }
        setup_datepickers();
      }


});



