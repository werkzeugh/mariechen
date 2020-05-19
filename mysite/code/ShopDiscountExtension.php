<?php 


use SilverStripe\ORM\DataExtension;



/**
* 
*/
class ShopDiscountExtension extends DataExtension
{
    
    
    
    public function LocalDiscountValue($fieldname)
    {
        //returns value or 'default'

        $val=$this->owner->getField($fieldname);    
        if(is_numeric($val))
        {
           $ret=(int) $val; 
        }
        else
            $ret= 'default';
        
        return $ret;
        
    }
    
    public function ParentDiscountValue($fieldname)
    {

        if($this->owner->Parent()->hasMethod('FallbackedDiscountValue'))
        {
            return $this->owner->Parent()->FallBackedDiscountValue($fieldname);
        }
        return $this->owner->getDefaultValueFor($fieldname);
    }
    
    
    public function getDefaultValueFor($fieldname)
    {
        switch ($fieldname) {
            case 'KennenlernDiscount':
                return 20;
                break;
            case 'MaxDiscount':
                return 70;
                break;
            
            default:
                return 0;
                break;
        }
    }

    public function FallbackedDiscountValue($fieldname)
    {
        $val=$this->owner->LocalDiscountValue($fieldname);
        if($val==='default')
        {
            $val=$this->owner->ParentDiscountValue($fieldname);
        }
        
        return $val;
        
    }
    
    
    
    public function FallbackedDiscountValueExplained($fieldname)
    {

        $val=$this->owner->LocalDiscountValue($fieldname);
        
        if($val==='default')
        {
            $ret.="<span style='color:#aaa' title='↓ = vererbter Wert'>↓ ".$this->owner->ParentDiscountValue($fieldname)."</span>";
        }
        else
            $ret.="<b>$val</b>";
        
        return $ret;
        
    }

    
    
	
}


?>
