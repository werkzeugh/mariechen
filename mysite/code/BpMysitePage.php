<?php

use SilverStripe\View\SSViewer;


class BpMysitePageController extends BpPageController
{
  
   public function init()
    {
      parent::init();
      $this->summitSetTemplateFile('main','BackendPage');
    }

    public function getRawTabItems()
     {
       $items=Array(
         "10"=>"Main Column",
         "20"=>"Settings",
         );
        
     
       return $items;
     }


    public function step_10_plainFields()
    {
      //define all FormFields for step "Title"
      $p=Array(); // ------- new field --------
      $p['label']="Page-Title";
      $p['fieldname']="Title";
      $this->formFields[$p['fieldname']]=$p;

    }

    public function step_10()
    {

      $this->step_10_plainFields();

      $html="<div class='formsection'>
        <table class='ftable'>
        \$AllFormFields.RAW
        </table>
        </div>
        <div  class='space'>
        <a href='#' class='button save submit' ><span class='ui-icon-check tinyicon'></span>Save</a>
      </div>
        ";

      $tpl=SSViewer::fromString($html);
      $basefieldHTML=$this->renderWith($tpl);


      CElement::includeRequirements();

      return <<<HTML
        $basefieldHTML
        <!-- CElement Block BEGIN -->
      </form><!-- close form, we have a new one in our stuff -->
      <style>
      .actions {display:none;} /* hide savelink */
      td.image {display:none;}
      </style>
      <div id='CElementMainColumnList' class='CElementList'></div>

      <script type="text/javascript" charset="utf-8">
      $(document).ready(function() {

        $('#CElementMainColumnList').CElement({
          fieldname:'MainColumn',
          record_id:'{$this->record->ID}',
          default_CType:'Absatz',
          allowed_CTypes:{ 
          'Text':'Text',
          'ImageText':'ImageText',
          'Image':'Image',
          'Video':'Video'
          }
        });

      });
      </script>
      <!-- CElement Block END -->

HTML;


    }




    
   
  
 
}
