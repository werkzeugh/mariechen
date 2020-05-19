/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

var  LastUsedMwLinkField;


(function() {
    // Load plugin specific language pack
    tinymce.PluginManager.requireLangPack('mwlink');

    tinymce.create('tinymce.plugins.MwLinkPlugin', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceMwLink');
            ed.addCommand('mceMwLink', function() {

              var href, 
                  action = "insert",
                  se = ed.selection,
                  elm = ed.selection.getNode();


              // No selection and not in link
              if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A')) return;

              elm = ed.dom.getParent(elm, "A");
              if (elm !== null && elm.nodeName == "A") {
                  action = "update";
              }

              if (action == "update") {
                href = ed.dom.getAttrib(elm, 'href');
              }


              var chooserUrl = '/BE/MwLink/chooser';
              // if(this.options.ChooserUrl)
              // {
              //   chooserUrl=this.options.ChooserUrl;
              // }
             
              if(href)
              {
               chooserUrl+='?MwLink=' + escape(href);
              }

              var iframe = $('<iframe src="' + chooserUrl + '" style="width:700px;height:500px" ></iframe>');
       

              var tinyMCEPopup={
                 'editor' : ed,
                 'execCommand': function(d, c, e, b) {
                       b = b || {};
                       b.skip_focus = 1;
                       // this.restoreSelection();
                       return this.editor.execCommand(d, c, e, b);
                   }
              };

              var popupwin = new Boxy(iframe, {
                            title: 'set Link',
                            afterHide: function() {
                              
                            },
                            modal: true,
                            x: 50,
                            y:jQuery(document).scrollTop() + 30
                        });
              //set global var LastUsedMwLinkField to a fake-function-holder
              LastUsedMwLinkField = {
                 'updateIDFromPopupWindow' : function(mwlink) {

                    var inst = ed;
                      var elm, elementArray, i;
                      elm = inst.selection.getNode();
                      elm = inst.dom.getParent(elm, "A");
                     if(popupwin.hide)
                      {                     
                        popupwin.hide();
                      }
                      // Remove element if there is no href
                      if (!mwlink) {
                        i = inst.selection.getBookmark();
                        inst.dom.remove(elm, 1);
                        inst.selection.moveToBookmark(i);
                        tinyMCEPopup.execCommand("mceEndUndoLevel");

                        return;
                      }

                      // Create new anchor elements
                      if (elm === null) {
                        inst.getDoc().execCommand("unlink", false, null);
                        tinyMCEPopup.execCommand("mceInsertLink", false, "#mce_temp_url#", {skip_undo : 1});

                        elementArray = tinymce.grep(inst.dom.select("a"), function(n) {return inst.dom.getAttrib(n, 'href') == '#mce_temp_url#';});
                        for (i=0; i<elementArray.length; i++)
                          this.setAllAttribs(elm = elementArray[i],mwlink);
                      } else
                        this.setAllAttribs(elm,mwlink);

                      // Don't move caret if selection was image
                      if (elm.childNodes.length != 1 || elm.firstChild.nodeName != 'IMG') {
                        inst.focus();
                        inst.selection.select(elm);
                        inst.selection.collapse(0);
                      }

                      tinyMCEPopup.execCommand("mceEndUndoLevel");
                    return null;
                  },
                  'setAllAttribs':function(elm,mwlink) {
                                        var href = mwlink.replace(/ /g, '%20');
                                        ed.dom.setAttrib(elm, 'href', href);
                                  }
               };

               

                              
            });

            // Register mwlink button
            ed.addButton('mwlink', {
                title : 'mwlink.desc',
                cmd : 'mceMwLink',
                image : url + '/img/mwlink.png'
            });

            // Add a node change handler, selects the button in the UI when a image is selected
            ed.onNodeChange.add(function(ed, cm, n, co) {
                cm.setDisabled('mwlink', co && n.nodeName != 'A');
                cm.setActive('mwlink', n.nodeName == 'A' && !n.name);
            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

       

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'MwLink plugin',
                author : 'Some author',
                authorurl : 'http://tinymce.moxiecode.com',
                infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/mwlink',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('mwlink', tinymce.plugins.MwLinkPlugin);
})();
