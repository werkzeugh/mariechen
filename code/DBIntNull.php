<?php

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBInt;

class IntNull extends DBInt
{
    public function __construct($name = null, $defaultVal = null)
    {
        parent::__construct($name);
        $this->defaultVal = is_int($defaultVal) ? $defaultVal : null;
    }


    public function exists()
    {
        return is_numeric($this->value);
    }
    
    /**
     * Return an encoding of the given value suitable for inclusion in a SQL statement.
     * If necessary, this should include quotes.
     */
    public function prepValueForDB($value)
    {
        if ($value === true) {
            return 1;
        }
        if (!is_numeric($value)) {
            if (strpos($value, '[')===false) {
                return $this->nullValue();
            } else {
                return Convert::raw2sql($value);
            }
        } else {
            return Convert::raw2sql($value);
        }
    }
    
    public function nullValue()
    {
        return 'NULL';
    }


    
    public function requireField()
    {
        $parts = [
            'datatype' => 'int',
            'precision' => 11,
            'null' => 'null',
            'default' => $this->defaultVal,
            'arrayValue' => $this->arrayValue
        ];
        $values = ['type' => 'int', 'parts' => $parts];
        DB::require_field($this->tableName, $this->name, $values);
    }
}
