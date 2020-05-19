<style>
#jsTree .current > a {font-weight:bold;}
</style>

<% require javascript(Mwerkzeug/thirdparty/jstree/jquery.jstree.js) %>
<% require javascript(Mwerkzeug/thirdparty/jquery_plugins/jquery.cookie.js) %>



<div style='width:auto;margin-right:10px;min-width:160px' class='MwFileTree'>

  <!-- <div class='space'><a href='#' class='button createFolder'>create Folder</a></div> -->
  <div id='jsTree'>
    $folderTreeAsUL.RAW
  </div>


</div>  

<script type="text/javascript" charset="utf-8">
$(document).ready(function() {


    function moveNodeToNewParent(node,newparent)
    {
        
        <% if Top.ChooserMode %>
        return false;
        <% else %>
          $.post(
            "/BE/MwFile/ajaxMoveFolder", 
            { 
              'nodeid': $(node).attr('id'),
              'newparentid': $(newparent).attr('id')
            }
          );   
         <% end_if %>
         
    }
    
    function getContextMenuItemsForJsTree(conf) {

        <% if Top.ChooserMode %>
        var conf = {}
        <% else %>
        var conf = {
            "create": {
                "separator_before": false,
                "separator_after": false,
                "label": "<% _t('js__create_folder','Create New Sub-Folder') %>",
                "action": function(obj) {
                    this.create(obj);
                }
            },
            "rename": {
                                     "separator_before": false,
                                     "separator_after": false,
                                     "label": "<% _t('js__create_folder','Rename Folder') %>",
                                     "action": function(obj) {
                                         this.rename(obj);
                                     }
                                 },
            "remove": {
                "separator_before": false,
                "separator_after": false,
                "label": "<% _t('js__delete_folder','Delete Folder') %>",
                "action": function(obj) {
                    this.remove(obj);
                }
            }
        };
        <% end_if %>
        
        return conf;

    }

    $('#jsTree').bind("open_node.jstree", function (event, data) { //automatically open parent nodes too
      if((data.inst._get_parent(data.rslt.obj)).length) { 
        data.inst.open_node(data.inst._get_parent(data.rslt.obj), false,true); 
      } 
    }).bind("loaded.jstree", function (event, data) {
      var theTree=data.inst;
      var rootNodes=theTree._get_children(-1)
      rootNodes.each(function() //open all root-nodes
      {
        theTree.open_node(this,false,true);
      });
 
     //always open current node:
     <% if CurrentDirectory  %>
      theTree.open_node($('#$CurrentDirectory.ID'),false,true);
     <% end_if %>
      
    }).jstree({
        'themes': {
            'theme': "classic",
            'dots': true,
            'icons': true
        },
        'plugins': ["dnd", "themes", "html_data", "cookies", "crrm", "contextmenu"],
        'contextmenu': {
            "items": getContextMenuItemsForJsTree
        },
        <% if Top.ChooserMode %>
        "crrm": {
                  "move" : {
                     "check_move" : function(){ 
                       return false;
                       } 
                     }
                  },
        <% end_if %>
        "dnd": {
          "drag_target":".jstree-draggable",
          "drag_finish":function(data) {
            targetId=data.r.attr('id');
            $(data.o).closest('.MwFileItem').MwFileItem('moveFile',targetId);
          },
          "drag_check": function (data) { 
            return { after : false, before : false, inside : ('$CurrentDirectory.ID' != data.r.attr('id')) }; }
        }
    }).bind("create.jstree",
    function(e, data) {
        $.post(
        "/BE/MwFile/ajaxTreeAdd",
        {
            "id": data.rslt.parent.attr("id").replace("node_", ""),
            "position": data.rslt.position,
            "title": data.rslt.name,
            "type": data.rslt.obj.attr("rel")
        },
        function(r) {
            // console.dir(r);
            if (r.id > 0) {
                $(data.rslt.obj).attr("id", "node_" + r.id);
                window.location = '/BE/MwFile/listing/' + r.id;
            }
            else {
                $.jstree.rollback(data.rlbk);
            }
        });
        //end post;
    }).bind("remove.jstree",
    function(e, data) {
        
        if(confirm('are you sure you want to delete that folder ?'))
        {
        $.post(
        "/BE/MwFile/ajaxTreeRemove",
        {
            "id": data.rslt.obj.attr("id").replace("node_", "")
        },
        function(r) {
            // console.dir(r);
            if (r.errormsg) {
                alert(r.errormsg);
                $.jstree.rollback(data.rlbk);
            }
            window.location.reload();
        });
        //end post;
        }
        else
        {
            window.location.reload();
        }
        
      }).bind("rename.jstree",
    function(e, data) {
        
        if(confirm('are you sure you want to rename the folder ?'))
        {
            $.post(
            "/BE/MwFile/ajaxTreeRename",
            {
                "id": data.rslt.obj.attr("id").replace("node_", ""),
                "newname": data.rslt.new_name
            },
            function(r) {
                // console.dir(r);
                if (r.errormsg) {
                    alert(r.errormsg);
                    $.jstree.rollback(data.rlbk);
                }
                window.location.reload();
            });
        
         } 
         else
         {
             window.location.reload();
         }
        //end post;
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

          if(confirm('are you sure you want to move the folder ?'))
          {
              moveNodeToNewParent(data.rslt.o, data.rslt.np);
          }
          else
          {
              location.reload();
          }

      
      }); 

    //end bind;


  



$(".dragicon").attr('title',"drag to folder on left to move file");

});
//end docready

</script>




