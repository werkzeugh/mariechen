<?php

use SilverStripe\Control\Session;
use SilverStripe\Security\Permission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;

//angularjs compatible api


class BpC4P_ApiController extends BackendPageController
{
    var $record;
    var $cache;
    var $c4p_mode = 0;

    var $RequestParams = null;
    private static $allowed_actions = [
        'getRequestParams',
        'getSettings',
        'loadRecord',
        'saveorder',
        'customaction',
        'listdata',
        'utf8fix',
        'walk_recursive',
        'getitem',
        'removeitems',
        'hideitems',
        'unhideitems',
        'copyitems',
        'pasteitems',
        'get_allowed_types_to_add',
        'editform',
        'editsave',
        'callActionOnMultipleElements',
        'callActionOnElement',
        'ajaxCElement',
        'getAjaxCElementList',
        'getAugmentedConfig',
    ];


    public function init()
    {
        parent::init();
    }

    public function getRequestParams()
    {
        if ($this->RequestParams == null) {
            if (array_get($_POST, 'settings')) {
                $this->RequestParams = $_POST;
            } else {
                $jsonInput = file_get_contents('php://input');
                if ($jsonInput) {
                    $this->RequestParams = json_decode($jsonInput, 1);
                } else {
                    $this->RequestParams = array();
                }
            }
        }
    }

    public function getSettings()
    {

        if (!isset($this->cache[__FUNCTION__])) {
            $this->getRequestParams();

            if (is_array($this->RequestParams['settings'])) {
                $this->cache[__FUNCTION__] = $this->RequestParams['settings'];
            } else {
                $this->cache[__FUNCTION__] = array();
            }
        }
        return $this->cache[__FUNCTION__];
    }

    public function loadRecord($id = 0)
    {

        $settings = $this->getSettings();
        if (strstr($settings['c4p_record'], 'mwlink')) {
            $this->record = MwLink::getObjectForMwLink($settings['c4p_record']);
        }
        return is_object($this->record);
    }


    public function saveorder()
    {
        $ret = array('status' => 'ok');


        return $this->callActionOnElement('ng_savesortelements');
    }

    public function customaction()
    {
        return $this->callActionOnElement('ng_customaction' /*,Array('action' => $action)*/);
    }


    public function listdata()
    {

        $ret = array('status' => 'error');
        if ($this->loadRecord()) {
            if ($this->record) {
                $ret = $this->getAjaxCElementList($this->record, $this->Settings);
            }
        } else {
            $ret['msg'] = 'record not found';
        }

        $clipboardsize = 0;
        $cp = Mwerkzeug\MwSession::get('CElement_clipboard');
        if ($cp) {
            $clipboardsize = sizeof($cp);
        }
        $ret['serverinfo']['clipboardsize'] = $clipboardsize;


        if ($this->record->ID == 4089) {
            $ret['items'][0]['html'] = mb_convert_encoding($ret['items'][0]['html'], 'UTF-8', 'UTF-8');
        }


        header('content-type: application/json; charset=utf-8');
        $txt = @json_encode($ret);
        if (!$txt) {
            $ret = $this->utf8fix($ret);
            $txt = json_encode($ret);
        }
        echo $txt;
        exit();
    }

    public function utf8fix($value)
    {

        $cleaningFunc =
            function ($string) {
                return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            };

        return $this->walk_recursive($value, $cleaningFunc);
    }


    function walk_recursive($obj, $closure)
    {
        if (is_object($obj)) {
            $newObj = new stdClass();
            foreach ($obj as $property => $value) {
                $newProperty = $closure($property);
                $newValue = $this->walk_recursive($value, $closure);
                $newObj->{$newProperty} = $newValue;
            }
            return $newObj;
        } elseif (is_array($obj)) {
            $newArray = array();
            foreach ($obj as $key => $value) {
                $key = $closure($key);
                $newArray[$key] = $this->walk_recursive($value, $closure);
            }
            return $newArray;
        } else {
            return $closure($obj);
        }
    }




    public function getitem()
    {

        $ret = array('status' => 'error');
        if ($this->loadRecord()) {
            if ($this->record) {
                $ret = $this->getAjaxCElementList($this->record, $this->Settings, $this->RequestParams['id']);
            }
        } else {
            $ret['msg'] = 'record not found';
        }

        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        exit();
    }



    public function removeitems()
    {

        $this->callActionOnMultipleElements('remove');
    }

    public function hideitems()
    {


        $this->callActionOnMultipleElements('hide');
    }

    public function unhideitems()
    {


        $this->callActionOnMultipleElements('unhide');
    }


    public function copyitems()
    {

        $this->callActionOnElement('copySelectionToClipboard');
    }



    public function pasteitems()
    {

        $settings = $this->getSettings();
        $place = $settings['c4p_place'];

        if ($this->loadRecord()) {
            if ($this->record) {
                $fieldname = $this->record->C4P->getFieldnameForPlace($place);
                $celement = new CElement($this->record, $fieldname);

                $celement->pasteSelectionFromClipboard($this->RequestParams);
            }
        }
    }


    public function get_allowed_types_to_add($requestParams)
    {
        $ret = array(
            'status'  => 'ok',
            'payload' => [],
        );
        $settings = $this->getSettings();
        $allowedTypes = $this->RequestParams['allowedTypes'];

        if ($this->loadRecord()) {
            $place = $settings['c4p_place'];
            if (preg_match('#/_children_?([^/]*)$#', $place, $m)) {
                $placeShortName = $m[1];
                $place = preg_replace('#/_children[^/]*$#', '', $place);
            }
            if (!$placeShortName) {
                $placeShortName = $place;
            }

            if (strstr($place, '/')) {
                $fieldname = $this->record->C4P->getFieldnameForPlace($place);
                $celement = CElement::getCElement($this->record, $fieldname);
                $parentElement = $celement;
            } else {
                $parentElement = $this->record;
            }

            $allowedTypesChecked = array();
            $context = array(
                'place'    => $place,
                'nextItem' => $this->RequestParams['nextItem'],
            );
            foreach ($allowedTypes as $typeKey => $type) {
                $context['place'] = $placeShortName;
                $context['type'] = preg_replace('#^.*C4P_#', '', $typeKey);
                $context['longType'] = $typeKey;

                if (Permissions::canDoAction('insertC4P', $parentElement, $context)) {
                    $allowedTypesChecked[$typeKey] = $type;
                }
            }
            $ret['payload'] = $allowedTypesChecked;
        }


        header('content-type: application/json; charset=utf-8');
        $ret = json_encode($ret);
        echo str_replace('[]', '{}', $ret);
        die();
    }




    public function editform()
    {

        // $params['do_not_resolve_aliases']=1;

        return $this->callActionOnElement('ng_edit');
    }


    public function editsave()
    {

        $params['no_json_encode'] = 1;
        // $params['do_not_resolve_aliases']=1;

        $ret = $this->callActionOnElement('ng_save', $params);
        return $ret['html'];
    }

    public function callActionOnMultipleElements($action, $params = array())
    {

        $ret = array('status' => 'error');
        $settings = $this->getSettings();

        if ($this->loadRecord()) {
            if ($this->record) {
                $place = $settings['c4p_place'];
                $fieldname = $this->record->C4P->getFieldnameForPlace($place);
                $ids = $this->RequestParams['ids'];
                if (is_array($ids)) {
                    foreach ($ids as $id) {
                        $celement = CElement::getCElement($this->record, $fieldname, $id);
                        if ($celement) {
                            $args = array();
                            $ret['msg'][$id] = call_user_func_array(array($celement, $action), $args);
                            $ret['status'] = 'ok';
                        } else {
                            $ret['msg'][$id] = 'celement not found for ' . $id;
                        }
                    }
                }
            }
        } else {
            $ret['msg'] .= 'record not found';
        }

        if ($params['no_json_encode']) {
            return $ret;
        } else {
            header('content-type: application/json; charset=utf-8');
            echo json_encode($ret);
            exit();
        }
    }

    public function callActionOnElement($action, $params = array())
    {
        $ret = array('status' => 'error');
        $settings = $this->getSettings();
        if ($this->RequestParams['id']) {
            $elementId = $this->RequestParams['id'];
        } elseif ($this->RequestParams['ids']) {
            $elementId = $this->RequestParams['ids'][0];
        }


        $paramsViaRequest = $this->RequestParams['params'];
        if ($this->loadRecord()) {
            if ($this->record) {
                $place = $settings['c4p_place'];
                $fieldname = $this->record->C4P->getFieldnameForPlace($place);
                // echo "<li>place:$place";
                // echo "<li>fieldname:$fieldname";
                $celement = CElement::getCElement($this->record, $fieldname, $elementId, $params);
                if (!$celement && $paramsViaRequest['newitem_duplicateof']) {
                    $old_celement_id = $paramsViaRequest['newitem_duplicateof'];
                    $celement2copy = CElement::getCElement($this->record, $fieldname, $old_celement_id);
                    if ($celement2copy) {
                        $defaultType = get_class($celement2copy);
                        $celement = new $defaultType($this->record, $fieldname, $elementId);
                        $celement->record = $celement2copy->record;
                    } else {
                        $ret['msg'] .= " object to duplicate not found";
                    }
                } elseif (!$celement && $paramsViaRequest['newitem']) {
                    $newitemDefaults = $paramsViaRequest['newitem_defaults'];
                    if ($newitemDefaults['ctype']) {
                        $defaultType = $newitemDefaults['ctype'];
                    } else {
                        $defaultType = $this->record->C4P->getDefaultTypeForPlace($place);
                        if (!$defaultType) {
                            $ret['msg'] .= " defaulttype cannot be determined for place:$place";
                        }
                    }
                    if (class_exists($defaultType)) {
                        $celement = new $defaultType($this->record, $fieldname, $elementId);
                    } else {
                        $ret['msg'] .= " class $defaultType is not defined";
                    }
                }
                if ($celement) {
                    $args = array();
                    if ($paramsViaRequest['newitem'] || $paramsViaRequest['action'] || $paramsViaRequest['nextaction'] || $paramsViaRequest['options'] || $paramsViaRequest['edit_json']) {
                        $args[0] = $paramsViaRequest;
                    }
                    if ($this->RequestParams['items']) {
                        $args[0] = $this->RequestParams;
                    }
                    $callResponse = call_user_func_array(array($celement, $action), $args);
                    if (is_array($callResponse) && $callResponse['status']) {
                        $ret = $callResponse;
                    } else {
                        if (is_object($callResponse)) {
                            $ret['html'] = $callResponse->RAW();
                        } else {
                            $ret['html'] = $callResponse;
                        }
                        $ret['status'] = 'ok';
                    }
                } else {
                    $ret['msg'] .= 'celement not found ' . $elementId;
                }
            }
        } else {
            $ret['msg'] .= 'record not found';
        }

        if ($params['no_json_encode']) {
            return $ret;
        } else {
            header('content-type: application/json; charset=utf-8');
            echo json_encode($ret);
            exit();
        }
    }



    public function ajaxCElement() // analog zu CElement::dispatch($Controller)
    {
        //c4p enabled version of ajaxCElement


        $action = Controller::curr()->urlParams['OtherID'];
        preg_match("#([^-]+)-([^-]+)-(-?\d+)#", Controller::curr()->urlParams['ID'], $m);
        $celement_id = $m[3];


        $record = MwLink::getObjectForMwLink($this->Settings['c4p_record']);
        if (!$record) {
            die('record cannot be loaded');
        }

        $place = $this->Settings['c4p_place'];
        $fieldname = $record->C4P->getFieldnameForPlace($place);


        // load child-celement if needed ---------- BEGIN
        $child_id = array_get($_POST, 'args.child_id');
        if ($child_id) {
            $child_ids = explode('/', "$child_id");

            $celement_id = array_shift($child_ids);
            $celement = CElement::getCElement($record, $fieldname, $celement_id);
            if ($celement) {
                $celement = $celement->getChildByID(implode('/', $child_ids));
            }

            if (!$celement) {
                die("cannot load child " . $child_id);
            }
        } else {
            $celement = CElement::getCElement($record, $fieldname, $celement_id);
        }

        // load child-celement if needed ---------- END


        if (!$celement) {
            $defaultType = $record->C4P->getDefaultTypeForPlace($place);
            if (!$defaultType) {
                die("defaulttype cannot be determined for place:$place");
            }

            if (class_exists($defaultType)) {
                $celement = new $defaultType($record, $fieldname, $celement_id);
            } else {
                die("class $defaultType is not defined");
            }

            if (array_get($_REQUEST, 'duplicateOf')) {
                // ------------------- duplicate C4P BEGIN --------------------
                preg_match("#([^-]+)-([^-]+)-(-?\d+)#", array_get($_REQUEST, 'duplicateOf'), $m);
                $old_celement_id = $m[3];
                $celement2copy = CElement::getCElement($record, $fieldname, $old_celement_id);

                if ($celement2copy) {
                    $defaultType = get_class($celement2copy);
                    $celement = new $defaultType($record, $fieldname, $celement_id);
                    $celement->record = $celement2copy->record;
                    $celement->write(array('insertAfter' => $old_celement_id));
                }
                // ------------------- duplicate C4P END ￼--------------------
            }
        }

        $args = array(); //no arguments so far (url completely parsed)
        echo call_user_func_array(array($celement, $action), $args);
        exit();
    }

    public static function getAjaxCElementList($Mainrecord, $Settings = null, $id = null)
    {

        $ret = array('status' => 'ok');

        $placename = $Settings['c4p_place'];

        $Fieldname = $Mainrecord->C4P->getFieldnameForPlace($placename);

        $items = array();
        if ($id) {
            $params['resolve_aliases'] = true;
            $item = CElement::getCElement($Mainrecord, $Fieldname, $id, $params);
            if ($item) {
                $items = array($item);
            }
        } else {
            $items = CElement::getCElementsForField($Mainrecord, $Fieldname, array('include_hidden' => 1));
        }

        $ret['items'] = array();

        $style = null;
        if ($Settings['placeconf'] && $Settings['placeconf']['style']) {
            $style = $Settings['placeconf']['style'];
        }

        foreach ($items as $item) {
            if (!$item || !($item->hasMethod('getBEPreviewHTML'))) {
                continue;
            }
            if (is_object($item) && $item->hasMethod('getBEPreviewHTML')) {
                $rec = array();
                $rec['ctype'] = $item->CType;
                $rec['nice_ctype'] = preg_replace('#^.*C4P_#', '', $item->CType);
                $rec['is_alias'] = ($item->AliasTo ? true : false);
                $rec['hidden'] = ($item->Hidden ? true : false);
                $rec['locked'] = ($item->Locked ? true : false);
                $rec['id'] = $item->ID;
                $rec['html'] = $item->getBEPreviewHTML($style);
                $rec['config'] = self::getAugmentedConfig($item);
                $rec['permissions'] = $item->getPermissions();


                if ($rec['config']['_children'] && $rec['config']['_children']['enable_treesort']) {
                    $rec['_children'] = array();
                    foreach ($item->getChildren() as $childItem) {
                        $childrec = array();
                        $childrec['ctype'] = $childItem->CType;
                        $childrec['nice_ctype'] = preg_replace('#^.*C4P_#', '', $childItem->CType);
                        $childrec['is_alias'] = ($item->AliasTo ? true : false);
                        $childrec['hidden'] = $childItem->Hidden;
                        $childrec['locked'] = $childItem->Locked;
                        $childrec['id'] = $childItem->ID;
                        $childrec['html'] = $childItem->getBEPreviewHTML();
                        $childrec['config'] = $childItem->getConfig();
                        $childrec['permissions'] = $item->getPermissions();

                        $rec['_children'][] = $childrec;
                    }
                }

                $ret['items'][] = $rec;
            }
        }
        $ret['fieldname'] = $Fieldname;

        if ($id) { //return only single item if id was given
            $ret['item'] = array_pop($ret['items']);
            unset($ret['items']);
        }


        //permissions:

        if (strstr($Fieldname, '/')) {
            $pathParts = explode('/', trim($Fieldname, '/'));

            $placename = array_pop($pathParts);
            $path = implode('/', $pathParts);

            $subElement = CElement::getCElement($Mainrecord, $path);
        }

        if ($subElement) {
            $parentRecord = $subElement;
        } else {
            $parentRecord = $Mainrecord;
        }

        $context = array(
            'placename' => $placename,
            'settings'  => $Settings,
        );
        foreach (array('c4p_reorder', 'c4p_multiselect', 'c4p_add') as $actionName) {
            $ret['permissions'][$actionName] = Permissions::canDoAction($actionName, $parentRecord, $context);
        }


        return $ret;
    }

    public static function getAugmentedConfig($item)
    {
        $conf = $item->getConfig();
        if (!$item->hasFormFields()) {
            $conf['no_editfields'] = 1;
        }
        return $conf;
    }
}
