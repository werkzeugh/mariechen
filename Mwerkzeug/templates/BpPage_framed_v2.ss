<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />

    $MetaTags


    
</head>

<body ng-controller="pageframeMainCtrl" id="body">


<div id="notificationBubble"><span class="msg">notificationBubble</span></div>


<% include BackendPage_Header %>

    <div id="mainholder" class="fullheight">
        <div id="sidebar">
           <iframe id='leftframe'  class="fullheight"name='leftframe'   src='/BE/Pages/treeframe'></iframe>
       </div>

          <div id="sidebar-resizer" 
          resizer="vertical" 
          resizer-width="10" 
          resizer-left="#sidebar" 
          resizer-right="#content"
          resizer-max="800">
        </div>
       
       <div id="content">
          <iframe id='rightframe'  class="fullheight"name='rightframe' src='/BE/Pages/edit'></iframe>
      </div>

  </div>

<script>

  var app={clipboard:[]};

  var setPageTreeRef=function(ref) {
    app.pageTreeRef=ref;
  }
  
  var callAngularFunctionOnPagetree=function() {


      var func2Call=frames['leftframe'].callAngularFunctionOnPagetree;
      var args = Array.prototype.slice.call(arguments);
      if (func2Call) {
        func2Call.apply(this,args)     
      }

  }

  var callAngularFunction=function () {

    var args = Array.prototype.slice.call(arguments);
    var name=args.shift();
    if (window.console && console.log) { console.log('callAngularFunction',name, args);  }

    var scope = angular.element(document.getElementById("body")).scope();

    if (scope) {
      var \$q=angular.element('body').injector().get('\$q');

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
      return null;
    }

    return defered.promise;

  };

    var CurrentPageID;
    var setCurrentPageID=function(id)
    {
      if(id>0) {
       callAngularFunction('setCurrentPageTreeId',id);
       CurrentPageID=id;
     }
   };

 var autosize=function(){

    var myHeight=$(window).height()-$('#header').height();

    $('#mainholder').height(myHeight);
    $('iframe').each(function(){
        $(this).css('height',myHeight);
    });
};

$(window).resize(function(){
    autosize();
});

$(document).ready(function($) {
    autosize();

    var navurl='/BE/Pages/treeframe';

    if(window.location.hash) {
        var editurl=window.location.hash;
        editurl=editurl.replace(/#/,'/BE/Pages/');
        $('#rightframe').attr('src',editurl);
        if(editurl.match('/edit/')) {
                navurl=editurl.replace('/edit/','/treeframe/'); 
            }
        }
        
        $('#leftframe').attr('src',navurl);

    });



</script>

</body>

</html>
