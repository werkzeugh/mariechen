<?php

use SilverStripe\Control\Session;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;

//
// <% if Top.ChooserMode %>
// <div style='text-align:right'><a href='#' class='button uselink' dbid='$ID' dburl='$Url'>&raquo; auswaehlen</a></div>
// <% end_if %>
//
//   <div class="control">
//     <ul>
//       <!-- <li><a href='/BE/MwFile$ChooserMode/ajaxRemoveFile/$ID' class='edit button tinyicon' title='edit metadata'><span class='ui-icon-pencil'></span></a></li> -->
//       <% if Top.ChooserMode %>
//       <% else %>
//       <li><a href='/BE/MwFile$ChooserMode/ajaxRemoveFile/$ID' class='delete button tinyicon' title='<% _t('js__removeFile','Remove this file') %>'><span class='ui-icon-trash'></span></a></li>
//       <% end_if %>
//       <li><a href='$Link' class='preview button tinyicon' title='zoom' target='_blank'><span class='ui-icon-zoomin'></span></a></li>
//     </ul>
//   </div>
//

class BpMwFile_ApiController extends BackendPageController
{
    private static $allowed_actions = [
        'get_files',
        'remove_files',
        'hide_files',
        'unhide_files',
        'sort_files'
    ];

    public function get_files(SilverStripe\Control\HTTPRequest $request)
    {
        $path=$request->getVar('path');

        $folder=MwFile::getByFilename($path);
        $payload=[];
        if ($folder) {
            foreach ($folder->getSortedChildren(["show_hidden"=>1]) as $f) {
                $payload[]=$this->getFileInfo($f);
            }
        }
       
        $ret=[
            "payload"=>$payload,
        ];
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        exit();
    }

    public function getFileInfo($f)
    {
        $ret=[
            "id"=>$f->ID,
            "filename"=>$f->Filename,
            "name"=>basename($f->Filename),
            "hidden"=>$f->Hidden?true:false,
        ];

        //thumbnail
        if ($img=$f->Image()) {
            if ($f->isSvg()) {
                // $ret['filename']=$f->Link();
            } elseif (is_array(array_get($_POST, 'Thumbnail')) && array_get($_POST, 'Thumbnail.format')) {
                //custom thumbnail
                $p=array_get($_POST, 'Thumbnail');
                if ($tn=$img->getFormattedImage($p['format'], $p['arg1'], $p['arg2'])) {
                    $ret['thumbnail_url']=$tn->Link();
                }
            } else {
                //default thumbnail
                if ($tn=$img->getFormattedImage("SetFittedSize", 200, 200)) {
                    $ret['thumbnail_url']=$tn->Link();
                }
            }
        }
        return $ret;
    }


    public function remove_files(SilverStripe\Control\HTTPRequest $request)
    {
        $q=json_decode($request->getBody(), 1);
        $file_ids=array_get($q, "file_ids", []);
        $path=array_get($q, "path", null);
        $removed_file_ids=[];
        if ($path) {
            $folder=MwFile::getByFilename($path);
            if ($folder) {
                foreach ($file_ids as $id) {
                    $f=MwFile::getByID($id);
                    if ($f && $f->ParentID==$folder->ID) {
                        $f->delete();
                        $removed_file_ids[]=$id;
                    }
                }
            }
        }
        
        
        $ret=[
            "payload"=>[
                "removed_file_ids"=>$removed_file_ids,
            ],
        ];
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        exit();
    }


    public function sort_files(SilverStripe\Control\HTTPRequest $request)
    {
        $q=json_decode($request->getBody(), 1);
        $file_ids=array_get($q, "sorted_ids", []);
        $path=array_get($q, "path", null);
        $handled_file_ids=[];
        if ($path) {
            $folder=MwFile::getByFilename($path);
            if ($folder) {
                $n=0;
                foreach ($file_ids as $id) {
                    $f=MwFile::getByID($id);
                    $n++;
                    if ($f && $f->ParentID==$folder->ID) {
                        $f->Sort=$n;
                        $f->write();
                        $handled_file_ids[]=$id;
                    }
                }
            }
        }
        
        
        $ret=[
            "payload"=>[
                "handled_file_ids"=>$handled_file_ids,
            ],
        ];
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        exit();
    }



    public function hide_files(SilverStripe\Control\HTTPRequest $request)
    {
        $q=json_decode($request->getBody(), 1);
        $file_ids=array_get($q, "file_ids", []);
        $path=array_get($q, "path", null);
        $handled_file_ids=[];
        if ($path) {
            $folder=MwFile::getByFilename($path);
            if ($folder) {
                foreach ($file_ids as $id) {
                    $f=MwFile::getByID($id);
                    if ($f && $f->ParentID==$folder->ID) {
                        $f->Hidden=1;
                        $f->write();
                        $handled_file_ids[]=$id;
                    }
                }
            }
        }
        
        
        $ret=[
            "payload"=>[
                "handled_file_ids"=>$handled_file_ids,
            ],
        ];
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        exit();
    }

    public function unhide_files(SilverStripe\Control\HTTPRequest $request)
    {
        $q=json_decode($request->getBody(), 1);
        $file_ids=array_get($q, "file_ids", []);
        $path=array_get($q, "path", null);
        $handled_file_ids=[];
        if ($path) {
            $folder=MwFile::getByFilename($path);
            if ($folder) {
                foreach ($file_ids as $id) {
                    $f=MwFile::getByID($id);
                    if ($f && $f->ParentID==$folder->ID) {
                        $f->Hidden=0;
                        $f->write();
                        $handled_file_ids[]=$id;
                    }
                }
            }
        }
        
        
        $ret=[
            "payload"=>[
                "handled_file_ids"=>$handled_file_ids,
            ],
        ];
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        exit();
    }
}
