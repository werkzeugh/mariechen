<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    $MetaTags
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base target="rightframe">
</head>
<body class="treeframe">

    <div class="scrollcontainer" style="position:relative;height: 100%;overflow:hidden">

        <form method="GET" action="" target="_self" id="reloadForm">
            <input type="hidden" name="treeGroupName" value="" id="treeGroupName">
            <button class="btn btn-xs reload-tree" type="submit" title="reload tree"><i class="fa fa-refresh"></i></button>
        </form>
        
        <form method="GET" id="actionform" action="/"><button class="btn btn-sm" type="submit" ng-click=""><i class="fa fa-check"></i> OK</button>
        </form>
        
        
        <div id="pagetree" class="pagetree jstree-default-dark" ng-controller="pagetreeMainCtrl"
        ng-init='init($settingsAsJson.RAW)'>
            <div class="treeContainer">
                <div jstree-chooser  app="app" id="pagetree-chooser" 
                                     label-edit-mode="<% _t('backend.labels.clickmode_edit')%>"
                                     label-preview-mode="<% _t('backend.labels.clickmode_preview') %>"
                                     label-click-mode="<% _t('backend.labels.clickmode') %>"


                ></div>
                $CustomTreeGroupHeader($settingsAsJson).RAW

                <div jstree  app="app" id="pagetree" selected-node="selectedTreeNode" ></div>

                $CustomTreeGroupFooter($settingsAsJson).RAW
            </div>
        </div>

    </div>

    <script>

        angular.bootstrap(document.getElementById('pagetree'), ['pagetree']);

        $('.scrollcontainer').perfectScrollbar();   

        callAngularFunctionOnPagetree=function () {

            var args = Array.prototype.slice.call(arguments);
            var name=args.shift();

            var scope = angular.element(document.getElementById("pagetree")).scope();

            if (scope) {
              var \$q=angular.element(document.getElementById("pagetree")).injector().get('\$q');

             if (window.console && console.log) { console.log('jljl',null);  }
              var defered=\$q.defer();
              scope.\$apply(function() {
               if (window.console && console.log) { console.log('apply name in scope',name,scope);  }
               var payload=null;
               res=name.match(/^app\.(.*)$/) ;
               if (res) {
                  name=res[1]
                  payload=scope.app[name].apply(this,args);
              } else {
                  payload=scope[name].apply(this,args);
              }
              defered.resolve({payload:payload,scope:scope});
          });
          } else {
             if (window.console && console.log) { console.log('no scope',null);  }
              return null;
          }

          return defered.promise;

      };

    </script>




</body>
</html>
