var back2edit;

var showBack2EditButtons=function() {

  var topWindow=window.parent;

  back2edit=function() {
      topWindow.callAngularFunctionOnPagetree('back2edit');    
  }
  console.log('#log 1743 BACK2EDIT',topWindow.app);

  if (topWindow.app) {
    document.write('<div class="back2edit"><button class="btn btn-primary btn-back2edit" onClick="back2edit()" type="button"><i class="fa fa-arrow-left"></i> <% _t('backend.labels.back2edit') %></button></div>');
  }



}

showBack2EditButtons();

