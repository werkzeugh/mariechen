var popupWindow = null;

if (typeof console == "undefined") {
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

var setup_datepickers;
$(document).ready(function() {

    $('a.helptiplink').addClass('ui-state-active');
    $('a.helptiplink').live('click',
    function(event) {
        event.preventDefault();
        url = $(this).attr('href');
        title = 'edit HelpTip';
        iframe = $('<iframe src="' + url + '" style="width:700px;height:700px" ></iframe>');
        popupWindow = new Boxy(iframe, {
            title: title,
            modal: true,
            afterHide: function() {
                location.reload();
            },
            x: 50,
            y:jQuery(document).scrollTop() + 30
        });

    });
    $("a.submit,button.submit").live('click',
    function(e) {
        e.preventDefault();
        if ($(this).attr('href').length > 2)
        {
          $('#dataform').attr('action', $(this).attr('href'));
        }
        // if (window.console && console.log) { console.log($('#dataform').attr('action'));  }
 
        $('#dataform').submit();
        return false;

    });

    $("a.formsubmit").live('click',
    function() {

        if ($(this).attr('href').length > 2)
        $(this).closest('form').attr('action', $(this).attr('href'));

        $(this).closest('form').submit();
        return false;

    });


    $("a.iframepopup").live('click',
    function(event) {
        event.preventDefault();
        url = $(this).attr('href');
        title = $(this).attr('title');
        if (!title)
        title = '&nbsp;';
        var afterHide;

        if ($(this).attr('afterHide'))
        {
            afterHide = window[$(this).attr('afterHide')];
        }

        if (!afterHide && typeof reloadList == 'function') {
            afterHide = reloadList;
        }

        iframe = $('<iframe src="' + url + '" style="width:700px;height:600px" ></iframe>');
        popupWindow = new Boxy(iframe, {
            title: title,
            afterHide: afterHide,
            modal: true,
            x: 50,
            y:jQuery(document).scrollTop() + 30
        });
    });


    $(document).keyup(function(event) {
        if (event.keyCode == 27) {
            //ESCAPE KEY CLOSES popupwindow
            if (popupWindow)
            popupWindow.hide();
        }
    });

    $("a.ajaxlink").live('click',
    function(event) {
        event.preventDefault();
        //no plain URL following here
        if ($(this).hasClass('delete'))
        {
          if (!confirm('Sind Sie sicher ?'))
             return false;
        }
        url = $(this).attr('href');
        var ajaxtarget = $(this).closest('.ajaxtarget');
        loadingdiv = $('<div class="loading"><div><img src="/Mwerkzeug/images/loading.gif"></div></div>').height(ajaxtarget.height());
        ajaxtarget.html(loadingdiv);
        $.ajax({
            type: "GET",
            url: url,
            success: function(data) {
                ajaxtarget.replaceWith(data);
            }
        });
    });

    $("a.confirm").live('click',
    function(event) {
        if (!confirm('Sind Sie sicher?')) {
            event.preventDefault();
            //no plain URL following here
        }
    });

    $(".disabled a").live('click',
    function(event) {
        event.preventDefault();
        //no plain URL following here
    });


    $("a.ajaxdelete").live('click',
    function() {
        url = $(this).attr('href');
        if (confirm('Sind Sie sicher?'))
        {
            $.ajax({
                type: "GET",
                url: url,
                success: function(msg) {
                    reloadList();
                }
            });
        }
        return false;
    });


    $(".helpbubble").each(function() {
        content = $(this).html();
        $(this).html('<div class="helpbubble_inner">' + content + '</div>');
    });


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
              showOn: "button",
              yearRange: '-2:+4',
              dateFormat: 'yy-mm-dd',
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
// end doc.ready



