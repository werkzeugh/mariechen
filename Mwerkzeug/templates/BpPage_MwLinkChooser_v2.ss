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
        
        <form method="GET" id="actionform" action="/">
        </form>
        
        <div id="pagetree" class="pagetree jstree-default-dark" ng-controller="pagetreeMainCtrl"
        ng-init='init($settingsAsJson.RAW)'>

            <div class="treeContainer">
                <div jstree-chooser  app="app" id="pagetree-chooser" ng-if="app.tabList && app.tabList.length>1"></div>
                <div jstree          app="app" id="pagetree" selected-node="selectedTreeNode" ></div>
            </div>
        
        </div>
    </div>
    <script>
        angular.bootstrap(document.getElementById('pagetree'), ['pagetree']);

        $('.scrollcontainer').perfectScrollbar();   
    </script>




</body>
</html>
