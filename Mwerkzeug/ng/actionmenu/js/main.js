require.config({
  paths: {
    'angular': '/Mwerkzeug/bower_components/angular/angular.min',
    'angular-bootstrap': '/Mwerkzeug/bower_components/angular-bootstrap/ui-bootstrap-tpls.min',
    'actionmenuApp': '/Mwerkzeug/ng/actionmenu/js/actionmenuApp'
  },
  shim: {
    actionmenuApp: {
      deps: ['angular-bootstrap']
    },
    'angular-bootstrap': {
      deps: ['angular']
    }
  }
});

require(['actionmenuApp'], function() {
  return angular.bootstrap(document.getElementById('actionmenuApp'), ['actionmenuApp']);
});
