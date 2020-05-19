require.config 
  paths:
    'angular': '/Mwerkzeug/bower_components/angular/angular.min'
    'bootstrap': '/Mwerkzeug/bower_components/bootstrap/dist/js/bootstrap.min'
    'angularModalService': '/Mwerkzeug/bower_components/angular-modal-service/dst/angular-modal-service'
    'translate': '/Mwerkzeug/bower_components/angular-translate/angular-translate.min'
    'translate-loader-static': '/Mwerkzeug/bower_components/angular-translate-loader-static-files/angular-translate-loader-static-files.min'
    'angular-validation':'/Mwerkzeug/bower_components/angular-validation-ghiscoding/dist/angular-validation'
    'translate-interpolation-messageformat': '/Mwerkzeug/bower_components/angular-translate-interpolation-messageformat/angular-translate-interpolation-messageformat.min'
    'messageformat-locale': '/Mwerkzeug/bower_components/messageformat/locale/de'
    'messageformat-wrapper': '/Mwerkzeug/ng/pageframe/js/MessageFormatWrapper'
    'MessageFormat': '/Mwerkzeug/bower_components/messageformat/messageformat'
    'oclazyload': '/Mwerkzeug/bower_components/oclazyload/dist/ocLazyLoad.require.min'
  shim:
    pageframeApp: 
      deps: ['angular','coreModule']
    coreModule:
      deps: ['angular','resizer','translate-interpolation-messageformat','translate-loader-static','oclazyload']
    'translate-loader-static':
      deps:['translate']
    'angular-validation':
      deps:[ 'translate-loader-static']
    oclazyload:
      deps: ['angular']
    resizer:
      deps: ['angular']
    translate:
      deps: ['angular']
    'messageformat-locale':
      deps: ['messageformat-wrapper']
    'messageformat-wrapper':
      deps: ['MessageFormat']
    'translate-interpolation-messageformat':
      deps: ['messageformat-locale','translate']
    angularModalService:
      deps: ['bootstrap']
      exports: 'module' 

require ['pageframeApp'], ->
  angular.bootstrap document,['pageframeApp']

