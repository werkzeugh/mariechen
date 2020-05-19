<?php

use Mwerkzeug\MwRequirements;
use SilverStripe\ORM\FieldType\DBEnum;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Session;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\View\SSViewer;
use SilverStripe\Versioned\Versioned;
use SilverStripe\i18n\i18n;
use SilverStripe\View\Requirements;
use SilverStripe\View\ViewableData;



/**
* 
*/
class EHP extends ViewableData
{
    
    var $cache;
    var $controller;
    var $record;
    var $FormFields;
    var $prefix;
    var $pagingMode='paged';

    function __construct($controller,$prefix='EHP')
    {
     $this->controller=$controller;
     $this->prefix=$prefix;
    }


    function __toString() 
    {
        return 'EHP for '.get_class($this->controller);
    }
    
    function getParent()
        {
            return $this->controller;
            
        }
        
    public function dispatch()
    {



        
        if(array_get($_POST,'action'))
        {
         
            $action=array_get($_POST,'action');
            $args=array_get($_POST,'args');
            if(!$args)
                $args=Array();
            return call_user_func_array(Array($this,$action),$args);
            
        }

    }


    public function getFilterFieldFor($fieldname,$conf)
    {
         
      if($conf['filter'])
        {
            

          $p=Array('fieldname'=>$fieldname);
            
           if(is_array($conf['filter']) && is_array($conf['filter']['options']))
               $p['options']=$conf['filter']['options'];
           if(is_array($conf['filter']) && is_array($conf['filter']['text_options']))
               $p['text_options']=$conf['filter']['text_options'];
            
           if($conf['width'])
               $p['styles']="width:".$conf['width']."px";
            
           if((is_array($conf['filter']) && $conf['filter']['type']=='auto'))
           {
               $rr=singleton($this->RecordClass);
              $fieldinfo=$rr->dbObject($fieldname);
              if($fieldinfo && $fieldinfo->class==DBEnum::class)
              {
                      $p['options']=$fieldinfo->enumValues();
              }
              elseif(preg_match('#^(.+)ID$#',$fieldname,$m))
              {
                  $name=$m[1];
                  $classname=$rr->has_one($name);
                  $p['options']=DataObject::get($classname)->map()->toArray();
              }
                  
           }
            
            //$p['default_value']=array_get($_POST,'filter')[$fieldname];
            $html=MwForm::render_naked_field($p);
        
            return $html;
        
        }
    }
    
    public function getColCount()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
        
            $tpl=$this->getRowTpl(NULL);
            $colcount= substr_count(strtolower($tpl), '<td');

           $this->cache[__FUNCTION__]=$colcount;
        }
        return $this->cache[__FUNCTION__];
        
    }
    
    public function getTotalColCount()
    {
        return $this->ColCount+2;
    }
    
    public function AllItems()
    {
        $this->pagingMode='none';
        $methodName="{$this->prefix}_Items";
        if($this->controller->hasMethod($methodName))
        {
            $items=$this->controller->$methodName($this->Options);
        }
        return $items;
    }
    
    public function xls_export($data=NULL)
    {
        
        $this->pagingMode='none';
        
        $options=array_get($_POST,'options');
        MwForm::preset(array_get($_POST,'filter'));
        MwForm::set_array_basename('filter');

        $items=$this->Items();
        
        
        if(!$data)
        {
            $data = array();
            foreach ($items as $item) {
                $data[]=$this->rowExportArr($item);
            }
        }
            
       
        
        // include package
        include 'Spreadsheet/Excel/Writer.php';

        // create empty file
        $excel = new Spreadsheet_Excel_Writer();

        // send client headers
        $excel->send('export'.Date('Y-m-d_H_i_s').'.xls');

        // add worksheet
        $sheet =& $excel->addWorksheet('Class I');

        // add data to worksheet
        $rowCount=0;
        foreach ($data as $row) {
            
            if(!$rowCount)//write headers
            {
                $colCount=0;
                
                foreach ($row as $key => $value) {
                    $value=utf8_decode($value);
                    $sheet->writeString($rowCount,  $colCount, $key);
                    $colCount++;
            
                }
                $rowCount++;
                
            }
            $colCount=0;
            
            foreach ($row as $key => $value) {
                $value=utf8_decode($value);
                $sheet->writeString($rowCount,  $colCount, $value);
                $colCount++;
            
            }
            
            $rowCount++;
        }

        // close and output file
        if ($excel->close() !== true) {
          echo 'ERROR: Could not save spreadsheet.';
        }

       
             //    
        // // include package
        // include 'Spreadsheet/Excel/Writer.php'; 
        // 
        // // create empty file
        // $excel = new Spreadsheet_Excel_Writer();
        // 
        // // send client headers
        // $excel->send('eventmessage_export'.Date('Y-m-d_H_i_s').'.xls');
        // 
        // // add worksheet
        // $sheet =& $excel->addWorksheet('Class I');
        // 
        // // add data to worksheet
        // $rowCount=0;
        // foreach ($data as $row) {
        //     
        //     if(!$rowCount)//write headers
        //     {
        //         $colCount=0;
        //         
        //         foreach ($row as $key => $value) {
        //             $value=utf8_decode($value);
        //               $sheet->write($rowCount,  $colCount, $key);
        //                $colCount++;
        //     
        //         }
        //         $rowCount++;
        //         
        //     }
        //     $colCount=0;
        //     
        //   foreach ($row as $key => $value) {
        //       $value=utf8_decode($value);
        //     $sheet->write($rowCount,  $colCount, $value);
        //      $colCount++;
        //     
        //   }
        //   $rowCount++;
        // }
        // 
        // // close and output file
        // if ($excel->close() !== true) {
        //   echo 'ERROR: Could not save spreadsheet.';
        // }

        die();
    }
    
    public function hasFilters()
    {
        $options=$this->getOptions();
        if($options['columns'])
        {
            foreach ($options['columns'] as $key => $value) {
                if($value['filter'])
                    return TRUE;
            }
            
        }
        if($options['hasExternalFilters'])
            return TRUE;
        
        return FALSE;
    }

    public function totalcount()
    {
        
        $options=array_get($_POST,'options');
        
        $items=$this->Items();
        $totalcount=$items->count();
        
        return $totalcount;
        die();
    }
    
    
    public function getVisibleColumns()
    {
        $methodName="{$this->prefix}_getVisibleColumns";
        if($this->controller->hasMethod($methodName))
        {
            return $this->controller->$methodName();
        }
        else
        {
            return Mwerkzeug\MwSession::get($this->getSessionKey().'_activeColumns');
        }
    }

    public function saveVisibleColumns($value,$request_type)
    {
        $methodName="{$this->prefix}_saveVisibleColumns";
        if($this->controller->hasMethod($methodName))
        {
            $items=$this->controller->$methodName($value,$request_type);
        }
        else
        {
            Mwerkzeug\MwSession::set($this->getSessionKey().'_activeColumns',$value);
            Mwerkzeug\MwSession::save();
            
        }
    }
    
    public function listing()
    {


        $options=array_get($_POST,'options');
        $loadcount=array_get($_POST,'loadcount');

        if(array_get($_POST,'columndata'))
        {
            $this->saveVisibleColumns(array_get($_POST,'columndata.fieldnames'),'post');
        }
        elseif($loadcount==0  && $options['columnData'])
        {
            $this->saveVisibleColumns($options['columnData']['fieldnames'],'onload');
        }

        
        MwForm::preset(array_get($_POST,'filter'));
        MwForm::set_array_basename('filter');

        if($loadcount==0 && $this->hasFilters())
        {
            $items=NULL; //no items for filterable tables on first load
            $rows='<tr><td colspan="'.$this->TotalColCount.'" class="ehp_reloadmarker"><center><img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"></center></td></tr>';
            $preload_phase=TRUE;
        }
        else
        {
            $items=$this->Items();
        }
                
        if(!$items)
            $items=new ArrayList();
            
        $items=new PaginatedList($items,$this->controller->getRequest());
        
        $items->setPageLength($this->getPageSize());

        $totalcount=$items->getTotalItems();
        $totalcount_str=($preload_phase)?'<img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">':$totalcount;
        
        foreach ($items as $item) {
            $rows.=$this->rowHtml($item);
        }
    
        if($totalcount==0 && !$rows)
          {
              $rows.="<tr><td colspan='{$this->TotalColCount}'><div class='ehp-noitemsfound'>no items found</div></td></tr>";
          }
     
        $colcount=0;
        
        if($this->getActiveColumns())
        {
            $headercols.="<th class='ehp-checkbox'></th>";
            $filtercols.="<th class='ehp-checkbox_empty'></th>";
            $colcount++;
            
            $end_js.="
                if(jQuery().tooltip)
                  {
                    $('table.ehptable th').tooltip(); 
                  }
                ";
          
            
            foreach ($this->getActiveColumns() as $key => $col_conf) {
                if($col_conf['label']=='auto')
                    $col_conf['label']='';
                $label=$col_conf['label']?$col_conf['label']:$key;
                
                $th_classes="";
                if($col_conf['sortable'])
                {
                    $th_classes="sortable_col";
                }
                
                if(array_get($_POST,'sortby') && array_get($_POST,'sortby')[$key])
                  $sortdata="data-sort='{array_get($_POST,'sortby')[$key]}'";
                else
                  $sortdata='';
                
                $styles='';
                if($col_conf['width'])
                {
                    $styles="width:{$col_conf['width']}px";
                }
                    
                $headercols.="<th title='{$col_conf['title']}' class='$th_classes' $sortdata data-fieldname='$key' style='$styles'>{$label}</th>";
                $colcount++;
                
                $p=Array();
                $p['fieldname']=$key;
                $filterfield=$this->getFilterFieldFor($key,$col_conf);
                $filtercols.="<th>$filterfield</th>";
            }

            $columnchooserbutton='';
            if($this->Options['columnChooser'])
            {
                
                foreach ($this->getAllColumns() as $fieldname => $fieldconf) {
                    $label=trim($fieldconf['label'])?$fieldconf['label']:$fieldname;
                    $column_rows.="<li ><a href=\"#\"><input type='checkbox' value='{$fieldname}' class='column_cb'> $label</a></li>";
                }
                
                $columnchooserbutton=<<<HTML
                <div class="btn-group MwCheckboxDropdown ehp-columnchooser">
                    <button class="btn dropdown-toggle btn-mini corner-all" data-toggle="dropdown" title="choose columns" >
                        <i class="icon-cog"></i>&nbsp;<span class="caret"></span>
                    </button>
                    <input type=hidden name="activeColumns" value="{$this->getActiveColumnNamesString()}" class='ehp-active-columns'>
                    <ul class="dropdown-menu">
                        <li><a href="#"><input type="checkbox" class='ehp-columnchooser-toggleall'> select all/none</a></li>
                        <li class='divider'></li>
                        $column_rows
                        <li class='divider'></li>
                        <li style='margin:5px 20px'><button class="btn btn-small ehp-columnchooser-apply" type="submit"><i class="icon-white icon-ok"></i> apply</button></li>
                    </ul>
                </div>
                
                <script type="text/javascript" charset="utf-8">
                    $('.ehp-columnchooser').MwCheckboxDropdown({preserveButtonTitle:true});
                    $('.ehp-columnchooser-toggleall').on('click',function(e){
                        e.preventDefault();
                        //get first setting:
                        var ddmenu=$(this).closest('.dropdown-menu');
                        if($('input.column_cb',ddmenu).first().is(":checked"))
                        {
                            $('input.column_cb',ddmenu).removeAttr('checked');
                        }
                        else
                        {
                            $('input.column_cb',ddmenu).attr('checked','1');
                        }

                    });
                </script>
                
HTML;
            }
            $headercols.="<th class='ehp_rowbuttons_th'>$columnchooserbutton</th>";
            $colcount++;

            $header="<tr>{$headercols}</tr>";

            if($options['use_bootstrap3_css'])
                $button="<a class=\"btn btn-sm btn-default submit_filter\" href='#' title='search'><i class=\"icon-search\"></i></a>";
            elseif($options['use_bootstrap_css'])
                $button="<a class=\"btn btn-small submit_filter\" href='#' title='search'><i class=\"icon-search\"></i></a>";
            else           
                $button="<a href='#' class='iconbutton submit_filter'><span class='tinyicon ui-icon-circle-arrow-e'></span>filter</a>";
            
            $filtercols.="<th style='min-width:60px'>$button</th>";
            if(strstr($filtercols,'filter['))
            {
                $filterrow="<tr class='filter'>{$filtercols}</tr>";
            }
        }

        $summary="
                   <tr class='plain summary'><td colspan='$colcount'><div style='text-align:right'>Total: <b class='ehp-totalcount'>{$totalcount_str}</b></div></td></tr>
                   ";
      
            
        // paging ---------- BEGIN
            

              
        $paging="";
        
        
        if($items->MoreThanOnePage())
        {
            if($items->NotFirstPage())
            {
                $link=preg_replace('#^.*\?start=#','#',$items->PrevLink());
                $paging.="<li><a href=\"{$link}\">«</a></li>";
            }
            else
                $paging.="<li class=\"disabled\"><a href=\"#\">«</a></li>";
                
            foreach ($items->PaginationSummary(8) as $p) {
                
                $p->Link=preg_replace('#^.*\?start=#','#',$p->Link);
                
                if ($p->CurrentBool) {
                    $paging.="<li class=\"active\"><a href=\"#\">{$p->PageNum}</a></li>";
                }
                else
                {
                    $paging.="<li><a href=\"{$p->Link}\" title=\"Go to page {$p->PageNum}\">{$p->PageNum}</a></li>";
                }
            }
            if($items->NotLastPage())
            {
                $link=preg_replace('#^.*\?start=#','#',$items->NextLink());
                $paging.="<li><a href=\"{$link}\">»</a></li>";
            }
            else
                $paging.="<li class=\"disabled\"><a href=\"#\">»</a></li>";
                
                
        }
        
            
        if($paging)    
            $paging="
                <tr class='plain ehp_pagination'><td colspan='$colcount' style='text-align:center'><div class='pagination'><ul>$paging</ul></div></td></tr>
        ";
                    
                
        // paging ---------- END
            
            

        $html= "<form method='POST' class='ehpform'><table class='ehptable ".$options['addon_table_classes']."'><thead>$summary$paging$header$filterrow</thead><tbody>$rows</tbody></table></form>";



        $addtext=$options['texts']['add_text']?$options['texts']['add_text']:'add Item';
        if($addtext!='none')
        {
          if($options['use_bootstrap3_css'])
              $html.="<div><a href='#' class='btn btn-default EHP_additem'><i class='fa fa-plus'></i> $addtext</a></div>";
          elseif($options['use_bootstrap_css'])
              $html.="<div><a href='#' class='btn btn_small EHP_additem'><i class='icon-plus'></i>$addtext</a></div>";
          else
              $html.="<div><a href='#' class='iconbutton EHP_additem'><span class='tinyicon ui-icon-plus'></span>$addtext</a></div>";

        }


        if($end_js)
        {
            $html.="<script>$end_js</script>";
        }
        echo $html;
        die();
    }
    
    
    public function Items()
    {
        $methodName="{$this->prefix}_Items";
        if($this->controller->hasMethod($methodName))
        {
            $items=$this->controller->$methodName($this->Options);
        }
        else
        {

            $methodName="{$this->prefix}_BaseItems";
            if($this->controller->hasMethod($methodName))
            {
                $items=$this->controller->$methodName($this->Options);
            }
            else
                $items=DataObject::get($this->RecordClass);
            
            
            if($sql=$this->getFilterSQL())
                $items=$items->where($sql);

            if($sql=$this->getSortSQL())
                $items=$items->sort($sql);
               
              
            if($joindata=$this->JoinArguments)
            foreach ($joindata as $jd) {
                        
                $func=array_shift($jd);
                if(strstr($func,'Join'))
                {
                    $items=call_user_func_array(array($items,$func),$jd);
                }
            }
        }
        return $items;
    }
    

    public function getPageSize()
    {
        $limit=$this->Options['pagesize'];
 
        if(!$limit)
          $limit=100;

        return $limit;
    }
    
    public function getPagingSQL()
    {
      
     if($this->pagingMode=='none')
            return "";
            
      $limit=$this->getPageSize();
     
      if(!array_key_exists('start',$_REQUEST) || !is_numeric(array_get($_REQUEST,'start')) || (int)array_get($_REQUEST,'start') < 1){
           $_REQUEST['start'] = 0;
        }
      $SQL_start = (int)array_get($_REQUEST,'start');

      $sql="{$SQL_start},$limit";

      return $sql;
    }

    public function getSortSQL()
    {

        $sortvars=array_get($_POST,'sortby');
        if($this->Options['dragdrop_sort'])
        {
            $conds[]="Sort asc";
        }          
        else
        {
            if($sortvars)
            foreach ($sortvars as $key => $value) {
                $conds[$key]=" $key $value";
            }
        }
      
        $options = array_get($_POST,'options');
        $methodName="{$this->prefix}_getSortSQL";
        if($this->controller->hasMethod($methodName))
        {
            $conds=$this->controller->$methodName($conds,$sortvars,$options);
        }
        
      
        if($conds)
            $sql=implode(',',$conds);
      
      
        
        return $sql;
    }

    public function getJoinArguments()
     {
    
         $methodName="{$this->prefix}_getJoinArguments";
         if($this->controller->hasMethod($methodName))
         {
             return $this->controller->$methodName($this->Options);
         }
         return NULL;
     }





    public function getFilterSQL($params=Array())
    {

        if ($params['filterVars']) {
            $filtervars=$params['filterVars'];
        } else {
            $filtervars=array_get($_POST,'filter');
            if(!$filtervars) {
                $filtervars=Array();
            }
        }
        
        if($this->RecordClass)  
            $tableobj=singleton($this->RecordClass);
        // run thru current columns and try to guess filters
        foreach ($filtervars as $key => $value) {
            if(is_string($value) && $key!='activeColumns')
            {
                $value=trim($value);
                if($value)
                {
                    $fieldname=Convert::raw2sql( $key );
                    // generate filter-sql for this field ---------- BEGIN
                    if($tableobj)
                    {
                        $field=$tableobj->dbObject($key);
                        if($field)
                        {
                           $fieldclass=get_class($field);
                        }
                    }
                
                    switch($fieldclass)
                    {
                        case DBBoolean::class:
                            $value=$value*1;
                            $cond[$key]=" `$fieldname` = ".Convert::raw2sql( $value )." ";
                        break;
                        case DBEnum::class:
                        $cond[$key] = " `$fieldname` = '".Convert::raw2sql( $value )."' ";
                        break;
                        case 'Int':
                        case 'Float':
                            $cond[$key]=" `$fieldname` = ".Convert::raw2sql( $value )." ";
                        break;
                    
                    
                    default:
                    if($value=='*')
                     $cond[$key]=" (`$fieldname` is not null  and `$fieldname` <>'') ";
                    elseif($value=='#empty')
                     $cond[$key]=" (`$fieldname` is null or `$fieldname`='' or `$fieldname`=0) ";
                    else
                     $cond[$key]=" lower(`$fieldname`) like lower ('%".Convert::raw2sql( $value )."%') ";
                    
                
                    }
                    // generate filter-sql for this field ---------- END
                }
            }
        }

        $options = array_get($_POST,'options');

        $methodName="{$this->prefix}_getFilterSQL";
        if($this->controller->hasMethod($methodName))
            {
             $this->controller->$methodName($cond,$filtervars,$options);
            }
        
        //if(array_get($_GET,'d') || 1 ) { $x=$cond; $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: <pre>$x</pre>"; }

        
        if($cond)
         return implode(' and ',$cond);
        

    }

    public function getColumnDefinitions()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
          $mycols=Array();
          $methodName="{$this->prefix}_Columns";
          if($this->controller->hasMethod($methodName))
          {
           $cols=$this->controller->$methodName();
           if (is_array($cols)) {
            foreach ($cols as $col) {
              if(is_array($col))
                $mycol=$col;
              else
               $mycol=Array('label' => $col);

              $mycols[]=$mycol;
            }
           }
          }  

         $this->cache[__FUNCTION__]=$mycols;

        }
        return $this->cache[__FUNCTION__];

        
    }

    public function getJSONColumnDefinitions()
    {
        $methodName="{$this->prefix}_getJSONColumnDefinitions";
        if($this->controller->hasMethod($methodName))
        {
            return $this->controller->$methodName();
        }
        
      return json_encode($this->ColumnDefinitions);
    }

    public function getHiddenFieldName()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
            $methodName="{$this->prefix}_getHiddenFieldName";
            if($this->controller->hasMethod($methodName))
                $this->cache[__FUNCTION__]= $this->controller->$methodName();
            else
                $this->cache[__FUNCTION__]= 'Hidden';

        }
        return $this->cache[__FUNCTION__];
    
    }

    public function isHidden($rec=NULL)
    {
        if($rec===NULL)
             $rec=$this->record;
        
        $hiddenFieldName=$this->getHiddenFieldName();
        $is_hidden=intval($rec->$hiddenFieldName);

        if(stristr($hiddenFieldName,'visible'))
        {
            $is_hidden=$is_hidden?0:1;
        }

        return $is_hidden;
    }


    public function getRecordClass()
    {
        $methodName="{$this->prefix}_getRecordClass";
        if($this->controller->hasMethod($methodName))
          return $this->controller->$methodName();
        else
          return 'Tag';
    }

    public function createNewRecord()
    {
        $classname=$this->getRecordClass();
        $rec=new $classname; // TODO which record
        
        $methodName="{$this->prefix}_createNewRecord";
        if($this->controller->hasMethod($methodName))
          return $this->controller->$methodName($rec);
        else
          return $rec;
    }

    public function add()
    {


        $this->record=$this->createNewRecord();        
	    if(!$this->record->Title && !$this->record->NoAutomaticTitleOnNewRecord)
	       $this->record->Title='new';

        return $this->edit();
    }


    public function savesort()
    {
        $sorted=0;
        $ids=array_get($_POST,'ids');
        if($ids)
        {
            foreach ($ids as $n=>$id) {
                $record=DataObject::get_by_id($this->getRecordClass(),$id);
                if($record)
                    {
                        $record->Sort=$n+1;
                        $record->write();
                        $sorted++;
                    } 
            }
        }

        return json_encode(Array('status' => 'ok','sortcount' => $sorted));

    }


    public function show()
    {
        
        
        if(array_get($_POST,'dbid') && !$this->record)
        {
            $methodName="{$this->prefix}_loadRecord";
            if($this->controller->hasMethod($methodName))
            {
              $this->record=$this->controller->$methodName(array_get($_POST,'dbid'));
            }
            else
                $this->record=DataObject::get_by_id($this->getRecordClass(),array_get($_POST,'dbid'));
        }
        
        $html =$this->rowHtml($this->record);
        return $html;
    }


      
    public function initFormFields()
    {
        static $hasBeenInited=0;
        
        $options = array_get($_POST,'options');
        
        if(!$hasBeenInited)
        {
             MwForm::set_default_rendertype('css');
                 
             MwForm::presetObject($this->record);
            $hasBeenInited=1;

            $methodName="{$this->prefix}_initFormFields";
        
            if($this->controller->hasMethod($methodName))
              $this->controller->$methodName($this->record,$this->FormFields,$this->Options);

        }

        
        
        
    }
    
    public function FormField($key)
    {
        $options = array_get($_POST,'options');
        if($options && $options['columns'])
        {
            $colconf=$options['columns'][$key];
        }
        
        $this->initFormFields();


        $fieldconf=$this->FormFields[$key];
 
        if(!$fieldconf)
        {
            $fieldconf=Array();
            $fieldconf['fieldname']=$key;
            
            $rr=singleton($this->RecordClass);
            $fieldinfo=$rr->dbObject($fieldconf['fieldname']);
            if($fieldinfo && $fieldinfo->class==DBEnum::class)
            {
                   $fieldconf['options']=$fieldinfo->enumValues();
            }
            
            
        }   
        
        if($colconf['label'] && !$fieldconf['label'] &&  $colconf['label']!='auto') 
        {
            $fieldconf['label']=$colconf['label'];
        }
        

        return new MwFormField($fieldconf);

    }

    public function getOptions()
    {
        return array_get($_POST,'options');
    }
    
    public function edit()
    {
        
      if(array_get($_POST,'dbid') && !$this->record)
      {


          $methodName="{$this->prefix}_loadRecord";
          if($this->controller->hasMethod($methodName))
          {
            $this->record=$this->controller->$methodName(array_get($_POST,'dbid'));
          }
          else
              $this->record=DataObject::get_by_id($this->getRecordClass(),array_get($_POST,'dbid'));
      }
      

      $methodName="{$this->prefix}_roweditHTML";
      
      if($this->controller->hasMethod($methodName)){
        $html=$this->controller->$methodName($this->record,$this->Options);
      }
      else
      {
          //alternative version 
          $methodName="{$this->prefix}_roweditTpl";
          if($this->controller->hasMethod($methodName))
              $ss_html=$this->controller->$methodName($this->record,$this->Options);

          $methodName="{$this->prefix}_roweditFormFields";
          if($this->controller->hasMethod($methodName))
              $this->FormFields=$this->controller->$methodName($this->record,$this->Options);

          
          if($ss_html)
          {
              $tpl=SSViewer::fromString($ss_html);
              $html=$this->customise($this->record)->renderWith($tpl);
          }
      
      } 
      


      
          $html="<tr dbid='{$this->record->ID}' class='editmode active ".$this->cssClasses4Row($this->record)."'  ".$this->tagData4Row($this->record)."><td class='ehp-checkbox'></td>
                 {$html}
                 <td class='ehp_rowbuttons ehp_buttons'>
                   <input type='hidden' name='fdata[ID]' value='{$this->record->ID}'>
                   <a href='#' class='iconbutton EHP_saveitem'><span class='tinyicon ui-icon-check'></span>OK</a>
                   <a href='#' class='iconbutton EHP_cancelitem'><span class='tinyicon ui-icon-close'></span>Close</a>

                 </td>
             </tr>";

      return $html;
    }

    public function cssClasses4Row($item)
    {
        $methodName="{$this->prefix}_cssClasses4Row";
        
        if($this->controller->hasMethod($methodName))
            return $this->controller->$methodName($item,$this->Options);
        
    }

    public function tagData4Row($item)
    {
        $methodName="{$this->prefix}_tagData4Row";
        
        $str="";
        if($this->controller->hasMethod($methodName))
        {
            $data=$this->controller->$methodName($item,$this->Options);
            if(is_array($data))
            foreach ($data as $key => $value) {
                $val=str_replace('"','&quot;',$value);
                $str.=" data-".strtolower(trim($key))."=\"{$val}\" ";
            }
        }
        
        return $str;
        
    }

    public function toggle_hidden()
    {
     
        $hiddenFieldName=$this->getHiddenFieldName();
      if(array_get($_POST,'dbid') && !$this->record)
      {
        $this->record=DataObject::get_by_id($this->getRecordClass(),array_get($_POST,'dbid'));
      }

      if($this->record && $this->record->hasField($hiddenFieldName))
      {
          
          if($this->record->$hiddenFieldName)
              $this->record->$hiddenFieldName=0;
          else
              $this->record->$hiddenFieldName=1;
          
          $this->record->write();
          
          
      }

      return '{"hidden":'.$this->isHidden().'}';
      
    }
    
    
    public function toggle_archive()
    {
        
      if(array_get($_POST,'dbid') && !$this->record)
      {
        $this->record=DataObject::get_by_id($this->getRecordClass(),array_get($_POST,'dbid'));
      }

      if($this->record->hasField('Archived'))
      {
        if($this->record->Archived)
          $this->record->Archived=0;
        else
          $this->record->Archived=1;
        
        $this->record->write();
      }
      return '{"archived":'.($this->record->Archived*1).'}';
    }
    

    public function duplicate()
    {
        
      Versioned::set_stage("Live");
    
      if(array_get($_POST,'dbid') && !$this->record)
      {
          $this->record=DataObject::get_by_id($this->getRecordClass(),array_get($_POST,'dbid'));
      }

      if($this->record)
      {
          $newrecord=$this->record->duplicate();
          if($newrecord->hasField('Title'))
                $newrecord->Title.=' (Copy)';
          if($newrecord->hasField('Hidden'))
                $newrecord->Hidden='1';
          
          $methodName="{$this->prefix}_onBeforeDuplicate";
          if($this->controller->hasMethod($methodName))
          {
              $this->controller->$methodName($newrecord,$this);
          }
          
          $newrecord->write();
          $this->record=$newrecord;
          return $this->show();
      }
      return "";
     
    }
    
    
    
    


   public function delete()
    {
        
      if(array_get($_POST,'dbid') && !$this->record)
      {
        $this->record=DataObject::get_by_id($this->getRecordClass(),array_get($_POST,'dbid'));
      }

      $methodName="{$this->prefix}_delete";
      if($this->controller->hasMethod($methodName))
      {
          $ret=$this->controller->$methodName($this->record);
          if($ret)
            return $ret;
      }
      else
          $this->record->delete();
          return "record deleted";
    }


    public function multi_action()
     {
       
       $action=array_get($_POST,'multiaction'); 
       
       $items=array_get($_POST,'ids');
             
       
       // perform action ---------- BEGIN
       
       // TODO check access
       
       // TODO check for EHP_... function from main-record
       
       $methodName="{$this->prefix}_multi_action";
       if($this->controller->hasMethod($methodName))
       {
           $ret=$this->controller->$methodName($action,$items);
           if($ret)
             return $ret;
       }
       
       switch($action)
       {
           case 'delete':
           foreach ($items as $id) {
               $item=DataObject::get_by_id($this->getRecordClass(),$id);
               if($item)
               {
                   $item->delete();
                   
                   $item=DataObject::get_by_id($this->getRecordClass(),$id);
                   if(!$item)
                   {
                     $msg.='<script> 
                     $("tr[dbid=\''.$id.'\']").fadeOut("slow",function(){
                       $(this).remove();
                      });
                    </script>';
                    $n++;
                    
                   
                   }
                   else
                     $msg.="<li>item #$id was not deleted.</li>";
                       
               }
               else
                   $msg.="<li>item #$id not found.</li>";
           }
           $msg.="$n item(s) deleted";
           break;
       
           default:
               $msg="sorry, i don't know how to perform the action '$action' ";
       }
       
       // perform action ---------- END
       
       return "$msg";
     }


    var $recordIsNew;
    var $fdata;
    
    var $errMsg;
    
    public function save()
    {
        
        parse_str(array_get($_POST,'formdata'),$formdata);

        if($formdata) {

            $this->fdata=$formdata['fdata'];
              
            //load record
            if($this->fdata['ID']) {
              $this->record=DataObject::get_by_id($this->getRecordClass(),$this->fdata['ID']);
            }
            else {
                //create new record for this
                $this->record=$this->createNewRecord();  
                $this->recordIsNew=1;
                $this->record->write();
                $this->fdata['ID']=$this->record->ID;
            }
            
            if($this->record)
            {
                //update record
                
                foreach ($this->fdata as $key => $value) {
                
                if(is_Array($this->fdata[$key]))
                {
                  foreach ($this->fdata[$key] as $key2=>$val2)
                    if(!$this->fdata[$key][$key2] || $this->fdata[$key][$key2]=="-1" )
                    unset($this->fdata[$key][$key2]);  //remove empties
                  $this->fdata[$key]=implode(',',$this->fdata[$key]);
                }
                
               }
                

               
               
                $this->record->update($this->fdata);
                             
                try {
                    $methodName="{$this->prefix}_onBeforeWrite";
                    if($this->controller->hasMethod($methodName))
                    {
                        $this->controller->$methodName($this->record,$this);

                    }
                    
                    
                    $this->record->write();
                    
                    $methodName="{$this->prefix}_onAfterWrite";
                    if($this->controller->hasMethod($methodName))
                    {
                        $this->controller->$methodName($this->record,$this);
                    }
                    
                    
                   } catch (Exception $e) {
                       return "<div class='space'><span class='alert alert-error'>error while saving record: <b>".$e->getMessage()."</b>\");</span></div>";
                   }   
                //save record
            }
        }

      $html=$this->rowHtml($this->record);
      $html.='<!-- SAVE_OK -->';

      return $html;
    }
    


    public function rowExportArr($item)
    {
    
        $methodName="{$this->prefix}_rowExportArr";
        if($this->controller->hasMethod($methodName))
        {
          return $this->controller->$methodName($item);
        }
        else
            return $item->toMap();
    }


    public function rowHtml($item)
    {
        static $tpl;
        if(!isset($tpl))
        {
            $tplHtml=$this->getRowTpl($item);
            $tpl=SSViewer::fromString($tplHtml);
        }

        if($tpl && $item)
        {
            $html=$this->customise($item)->renderWith($tpl);
            $rowButtons=$this->rowButtons($item);
            
            if($this->Options['dragdrop_sort'])
            {
              $rowButtons.=$this->defaultButton('sortgrip');
            }

            if($this->isHidden($item))
              $cssclasses.=' ishidden';

            if($item->Archived)
              $cssclasses.=' archived';
            
            $cssclasses.=$this->cssClasses4Row($item);
            $datatags.=$this->tagData4Row($item);


            $html="<tr dbid='{$item->ID}' class='$cssclasses' $datatags><td class='ehp-checkbox'></td>
            {$html}
            <td class='ehp_rowbuttons'><p>{$rowButtons}</p></td>
            </tr>";   
            return $html;
        }
        else
            return "<!-- no template  $tpl $item -->";
    }


    public function rowButtons($item)
    {
      $methodName="{$this->prefix}_rowButtons";
      if($this->controller->hasMethod($methodName))
      {
        return $this->controller->$methodName($item);

      }
      else
       return implode("\n",Array($this->defaultButton('inlineedit'),$this->defaultButton('delete')));
    }
   


    function tt($key,$txt_en,$txt_de=NULL)
    {
        static $lang;
        
        if(!$lang)
        {
            if($locale=i18n::get_locale())
              $lang=substr($locale,0,2);
            else
              $lang='de';
        }
        
        return ${"txt_$lang"};
    }
   
  
    public function getDefaultButtons()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
          $buttons=Array();
          $buttons['show']="<a href='%s' class='iconbutton' title='".$this->tt('show','show','zeigen')."'><span class='tinyicon ui-icon-search'></span></a>";
          $buttons['show_blank']="<a href='%s' target='_blank' class='iconbutton' title='".$this->tt('show','show','zeigen')."'><span class='tinyicon ui-icon-search'></span></a>";
          $buttons['edit']="<a href='%s' class='iconbutton edit' title='".$this->tt('inlineedit','edit','bearbeiten')."'><span class='tinyicon ui-icon-pencil'></span></a>";
          $buttons['sortgrip']="<a class='sortgrip'></a>";
          $buttons['inlineedit']="<a href='#' class='iconbutton EHP_edititem' title='".$this->tt('inlineedit','edit','bearbeiten')."'><span class='tinyicon ui-icon-pencil'></span></a>";
          $buttons['delete']="<a href='#' class='iconbutton EHP_deleteitem' title='".$this->tt('delete','delete','löschen
')."'><span class='tinyicon ui-icon-trash'></span></a>";
          $buttons['hide_unhide']="<a href='#' class='iconbutton EHP_toggle_hidden' title='".$this->tt('hide_unhide','hide/publish','verstecken/veröffentlichen')."'><span class='tinyicon ui-icon-cancel'></span></a>";
          $buttons['archive_unarchive']="<a href='#' class='iconbutton EHP_toggle_archive' title='".$this->tt('archive_unarchive','archive/unarchive','archivieren/unarchivieren')."'><span class='tinyicon ui-icon-lightbulb'></span></a>";
          $buttons['duplicate']="<a href='#' class='iconbutton EHP_duplicateitem' title='".$this->tt('duplicate','duplicate','duplizieren')."'><span class='tinyicon ui-icon-copy'></span></a>";
           
           $this->cache[__FUNCTION__]=$buttons;
        }
        return $this->cache[__FUNCTION__];
    }

    public function defaultButton()
    {
        $args = func_get_args();
        $key=array_shift($args);
        
        if($args)
        {   
            return vsprintf($this->DefaultButtons[$key],$args);
        }
        else
            return $this->DefaultButtons[$key];
    }

    public function getRowTpl($item)
    {                
        if(!isset($this->cache[__FUNCTION__]))
        {
            $methodName="{$this->prefix}_rowTpl";
        
            if($this->controller->hasMethod($methodName))
            {
                $TplHtml=$this->controller->$methodName($item);
                if($TplHtml)
                {
                    $html= $TplHtml;
                }
            }
            
            if(!$html)
            {
                $html=$this->getAutogeneratedRowTpl($item);
            }

            $this->cache[__FUNCTION__]=$html;
            
        }

        return $this->cache[__FUNCTION__];
        
    }
    
    public function getSessionKey()
    {
        return $this->prefix."_".get_class($this->controller);
    }

    public function getActiveColumnNamesString($value='')
    {
        return implode(',',$this->getActiveColumnNames());
    }
    
    public function getActiveColumnNames()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
            
            $names=$this->getVisibleColumns();
            if($names)
            {
                $names=explode(',',$names);
            }
            else
            {
                $cn=$this->getAllColumns();
                $names=Array();
                foreach ($cn as $columnname => $cn) {
                    if(!$cn['hide_on_load'])
                     $names[]=$columnname;
                }
            }
              

            $this->cache[__FUNCTION__]=$names;
        }
        return $this->cache[__FUNCTION__];

    }

    public function getAllColumns()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
          
            $columns=$this->options['columns'];
            if(!$columns)
                $columns=Array();
     
            $this->cache[__FUNCTION__]=$columns;
        }
        return $this->cache[__FUNCTION__];
        
    }
    
    
    public function getActiveColumns()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
            $columns=$this->getAllColumns();
            $columns2=Array();
            foreach ($this->getActiveColumnNames() as $fieldname) {
                $columns2[$fieldname]=$columns[$fieldname];
            }
            $this->cache[__FUNCTION__]=$columns2;
        }
        return $this->cache[__FUNCTION__];
    }
    
    
    public function getAutogeneratedRowTpl($item)
    {

        //loop thru all active Columns and create template
        $activeColumns=$this->getActiveColumns();
        foreach ($activeColumns as $field => $fieldconf) {
            $html.=$this->getTdTpl($item,$field);
        }

        return $html;

    }
    
    public function getTdTpl($item,$field)
    {
        return "<td class=\"ehp-col-$field\">".$this->getColumnTpl($item,$field)."</td>";
    }

    public function getColumnTpl($item,$field)
    {
        $ct=$this->columnTemplates();
        if($ct[$field])
        {
            return $ct[$field];
        }
        else
            return '$'.$field; //default - columntemplate
    }
    
    public function columnTemplates()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
            $tpls=Array();
            $methodName="{$this->prefix}_columnTemplates";
            if($this->controller->hasMethod($methodName))
            {
                $tpls=$this->controller->$methodName();
            }
            $this->cache[__FUNCTION__]=$tpls;
        }
        return $this->cache[__FUNCTION__];

    }


    static public function includeRequirements($params=NULL)
    {
    
        if(!$params['skip_bootstrap_setup'])
        {
            MwBackendPageController::includePartialBootstrap(Array('scripts'=>'all'));
        }
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.core.js');
        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.widget.js');
        Requirements::javascript("Mwerkzeug/javascript/jquery.ui.subclass.js");
        Requirements::javascript('Mwerkzeug/javascript/EHP_jqueryui_widget.js');
        MwRequirements::javascript('mysite/javascript/EHP_jqueryui_widget.js'); //try to load custom class

        Requirements::javascript('Mwerkzeug/javascript/MwCheckboxDropdown_jquery_plugin.js');

        if(i18n::get_locale()=="de_DE")
        {
            MwRequirements::javascript('Mwerkzeug/javascript/EHP_jqueryui_widget-de.js'); //try to load localization class
        }
      
        Requirements::CSS('Mwerkzeug/css/EHP.css');
        MwRequirements::CSS('mysite/css/EHP.css'); //try to load custom css
    }

}


 ?>
