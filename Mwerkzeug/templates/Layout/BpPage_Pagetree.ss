<style>

    #jsTree {font-size:12px;font-family:verdana,arial,sans-serif}
    
  #jsTree .current > a {font-weight:bold;}
  #jsTree a.hidden {color:#aaa;}
  #jsTree a.notinmenu .jstree-icon {opacity:0.3;}
  .reloadtree {position:absolute;right:15px;top:5px}
  #jsTree_wrap { width:100%;overflow:hidden;position:relative}

  <% if   UseFrames %>
     #jsTree_wrap {margin-top:10px}
  <% end_if %>
</style>

<% require javascript(Mwerkzeug/thirdparty/jstree/jquery.jstree.js) %>
<% require javascript(Mwerkzeug/thirdparty/jquery_plugins/jquery.cookie.js) %>
    
<div id='jsTree_wrap'>

    <% if   UseFrames %>
      <a href='#' class='button reloadtree' title='refresh tree'><span class='tinyicon ui-icon-refresh'></span></a>
    <% end_if %>
    
  <div id='jsTree'>
  </div> 
</div>  

<% include BpPage_Pagetree_local %>


<script type="text/javascript" charset="utf-8">
var useFrames;
<% if UseFrames %>

  useFrames=true;

  function getCurrentPageId()
  {
      return window.parent.CurrentPageID;
  }

  function setCurrentPageId(id)
  {
      
          return window.parent.setCurrentPageID(id);

  }
  
  function updateNode(id,name,newattr)
  {
      var node=$('#node_'+id);
      
       if (window.console && console.log) { console.log(newattr);  }
      if(node)
      {
       $('#jsTree').jstree('rename_node',node,name);
       $('>a',node).attr(newattr).removeClass().addClass(newattr.className);
      }
       
  }
  
<% else %>

    var CurrentPageID;
    function getCurrentPageId()
    {
        return CurrentPageID;
    }

    function setCurrentPageId(id)
    {
        return CurrentPageID = id;
    }
  

<% end_if %>


  
  
  
  $(document).ready(function() {

      var targetWindowName;
     <% if UseFrames %>
         targetWindowName='rightframe';
     <% else %>
         targetWindowName='_self';
     <% end_if %>
     
     $('#jsTree').bind("reopen.jstree", function (e, data) { 

          <% if AllParentNodesOfCurrentPage  %>
           var nodes2open={$AllParentNodesOfCurrentPage};
           
           var openNextNode=function()
           {
               if (nodes2open.length)
               {
                   data.inst.open_node("#"+nodes2open.pop(),openNextNode,true);
               }
               else
               {
                   $('#node_'+getCurrentPageId()).addClass('current');
                   
               }
           }
           openNextNode();
           
          <% end_if %>
      }).jstree({
      'themes' : {
        'theme' : "classic",
        'dots' : true,
        'icons' : true
      },
      'plugins' : [ "themes", "json_data",  "cookies" ,  "crrm", "dnd","contextmenu"],
      "json_data": {
          "ajax": {
              "url": "/BE/Pages/ajaxTreeData/",
              "data": function(n) {
                  return {
                      "id": n.attr ? n.attr("id").replace("node_", "") : 0,
                      "context": "$CurrentURL"
                  };
              }
          }
      },
      'contextmenu': {
        items : getContextMenuItemsForJsTree
      },
      'dnd': {
        'copy_modifier' : null,
        'drop_finish' : function () { 
          alert("DROP"); 
        },
        'drag_check' : function (data) {
          if(data.r.attr('id') == 'phtml_1') {
            return false;
          }
          return { 
            after : false, 
            before : false, 
            inside : true 
          };
        },
        'drag_finish' : function () { 
          alert('DRAG OK'); 
        }
      },
      "core": { 
				"initially_open" : $AllParentNodesOfCurrentPage  
			}
    }).bind("create.jstree", function (e, data) {
      $.post(
        "/BE/Pages/ajaxAdd", 
        { 
          "operation" : "create_node", 
          "id" : data.rslt.parent.attr("id").replace("node_",""), 
          "position" : data.rslt.position,
          "title" : data.rslt.name,
          "type" : data.rslt.obj.attr("rel")
        }, 
        function (r) {
          if(r.status) {
            data.inst.refresh($(data.rslt.obj).parent()[0]);
            $(data.rslt.obj).attr("id", "node_" + r.id);
            var editurl='/BE/Pages/edit/'+r.id;
            $("a",data.rslt.obj).attr("href", editurl);
            window.open(editurl,targetWindowName);
          }
          else {
            $.jstree.rollback(data.rlbk);
          }
        }
      ); //end post
    }).bind("move_node.jstree", function (e, data) {
      //
      // `data` contains:
      // .o - the node being moved
      // .r - the reference node in the move
      // .ot - the origin tree instance
      // .rt - the reference tree instance
      // .p - the position to move to (may be a string - "last", "first", etc)
      // .cp - the calculated position to move to (always a number)
      // .np - the new parent
      // .oc - the original node (if there was a copy)
      // .cy - boolen indicating if the move was a copy
      // .cr - same as np, but if a root node is created this is -1
      // .op - the former parent
      // .or - the node that was previously in the position of the moved node


      if(confirm('are you sure you want to move that page ?'))
       {
           updateNodeChildren(data.rslt.rt, data.rslt.cr);
       }
       else
       {
           location.reload();
       }
      
      }); 
      
      function updateNodeChildren(t,p)
      {
          if(p==-1)
          {
              var parentid='node_0';
          }
          else
          {
              var parentid=p.attr('id');
          }

          c=t._get_children ( p );
        
        var childrenids=new Array();
        
        c.each(function () {
          childrenids.push($(this).attr('id'));
        });

        $.post(
          "/BE/Pages/ajaxUpdateNodeChildren", 
          { 
            'id': parentid,
            'childrenids[]': childrenids
          }
        );   
      }

    function getContextMenuItemsForJsTree (node) {

      var conf={
        <% if isAllowed(createPages) %>
        "preview" : {
          "separator_before"  : false,
          "separator_after"   : false,
          "label"       : "View Page in new Window",
          "action"      : function (obj) { 
           var id=obj.attr("id").replace("node_", ""); 
           var url='/BE/Pages/show/' + id; 
           window.open(url,'_blank'); 
           }
        },
        "unhide" : {
          "separator_before"  : false,
          "separator_after"   : false,
          "label"             : "Publish this Page",
          "action"      : function (obj) { 
           var id=obj.attr("id").replace("node_", ""); 
           $.post("/BE/Pages/ajaxUnHide", {
                   "id": obj.attr("id").replace("node_", ""),
               });
           $('a',node).removeClass('hidden');
           }
        },
         "delete" : {
          "separator_before"  : false,
          "separator_after"   : false,
          "label"             : "Delete this Page",
          "action"      : function (obj) { 
             if(confirm('are you sure ?'))
             {
               var id=obj.attr("id").replace("node_", ""); 
               $.post("/BE/Pages/ajaxDelete", {
                       "id": obj.attr("id").replace("node_", ""),
                   });
              
               self=this;
               $('a',node).fadeOut(function()
               {
                  self.delete_node(obj);
               });
             }
           }
        },
        "hide" : {
          "separator_before"  : false,
          "separator_after"   : false,
          "label"             : "Hide this Page",
          "action"      : function (obj) { 
           $.post("/BE/Pages/ajaxHide", {
                   "id": obj.attr("id").replace("node_", ""),
               });
           $('a',node).addClass('hidden');
           }
        },
        "create" : {
          "separator_before"  : false,
          "separator_after"   : false,
          "label"       : "Create new Page in this Branch",
          "action"      : function (obj) {  this.create(obj); }
        },
        "duplicate" : {
          "separator_before"  : false,
          "separator_after"   : false,
          "label"       : "Duplicate Page",
          "action"      : function (obj) {  
              var tree=this;
             $.post("/BE/Pages/ajaxDuplicate", {
                 "id": obj.attr("id").replace("node_", ""),
             }, function(r) {
                 if (r.status) {
                     tree.refresh(obj.parent()[0]);
                     var editurl='/BE/Pages/edit/' + r.id;
                     window.open(editurl,targetWindowName);
                 } else {
                     alert('failed to duplicate the page');
                 }
               });
          }
        }
            <% if isAdmin %>
            ,"duplicate_with_children" : {
                "separator_before": false,
                "separator_after": false,
                "label": "Duplicate Page (recursive)",
                "action": function(obj) {
                    if (confirm('are you sure ?')) {
                        var tree            = this;
                        $.post("/BE/Pages/ajaxDuplicateWithChildren", {
                            "id": obj.attr("id").replace("node_", "")
                        },
                        function(r) {
                            if (r.status) {
                                tree.refresh(obj.parent()[0]);
                                var editurl = '/BE/Pages/edit/' + r.id;
                                window.open(editurl, targetWindowName);
                            } else {
                                alert('failed to recursively duplicate the page');
                            }
                        });
                    }
                }
            }
            <% end_if %>
            ,"versions" : {
              "separator_before"  : false,
              "separator_after"   : false,
              "label"             : "Page-History",
              "action"      : function (obj) { 
               var id=obj.attr("id").replace("node_", ""); 
               var url='/BE/Pages/versions/' + id; 
               window.open(url,'rightframe'); 
             }
           }
        <% end_if %>
      };
      
      if (typeof(handle_local_ContextMenu) == "function")
      {
          conf=handle_local_ContextMenu(conf,node);
      }
     
      if($('a',node).hasClass('hidden'))
      {
        delete conf.hide;
      }
      else
      {
        delete conf.unhide;
      }

      

      return conf;  

    }

    
    $('a.reloadtree').click(function(e){
        e.preventDefault();
        var navurl='/BE/Pages/treeframe';
        if(parent.location.hash)
        {
            var editurl=parent.location.hash;
            editurl=editurl.replace(/#/,'/BE/Pages/');
            if(editurl.match('/edit/'))
            {
                navurl=editurl.replace(/\\/edit\\//,'/treeframe/');
            }
        }
        window.open(navurl,'_self');

    });

    // simpleTreeCollection = $('div.simpleTree > ul').simpleTree({
    //    autoclose: true,
    //    drag:false,
    //    animate:true,
    //    docToFolderConvert:true,
    //    afterClick: function (obj){
    //      id=$(obj).attr("id");
    //      window.location='/BE/Pages/edit/'+id;
    //    }
    //  });

   });

</script>




