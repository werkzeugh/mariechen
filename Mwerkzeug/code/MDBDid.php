<?php
/* Please keep a link to http://azure-dev.kizone.net/648-mdb2

+ For MDB2 full compatibility, or we can remove this class and replace the functions query, exec and lastInsertId in the websites: +*/

class MDBDid_Result extends MySQLi_Result
{
    public function fetchRow()
    {
        return $this->fetch_assoc();
    }
 
    public function numRows()
    {
        return $this->num_rows;
    }
 
    public function seek($dplacement)
    {
        return $this->data_seek($dplacement);
    }
}
 
class MDBDid extends mysqli
{
    public function query($query, $resultmode = null)
    {
        $this->real_query($query);
        return new MDBDid_Result($this);
    }
    public function exec($query)
    {
        return parent::query($query);
    }
    public function lastInsertId()
    {
        return $this->insert_id;
    }
/*--*/
 
    public function getOne($query)
    {
        $res = parent::query($query);
        if ($res === false) {
            return ["error" => $this->error];
        }
        $range = $res->fetch_row();
        $one = $range[0];
        $res->close();
        return $one;
    }
 
    public function getRow($query)
    {
        $res = parent::query($query);
        if ($res === false) {
            return ["error" => $this->error];
        }
        $range = $res->fetch_assoc();
        $res->close();
        return $range;
    }
 
    public function getCol($query)
    {
        $res = parent::query($query);
        if ($res === false) {
            return ["error" => $this->error];
        }
        $rows = array();
        while (is_array($range = $res->fetch_row())) {
            $colonne[] = $range[0];
        }
        $res->close();
        return $colonne;
    }
 
    public function getAssoc($query)
    {
        $res = parent::query($query);
        if ($res === false) {
            return ["error" => $this->error];
        }
        $rows = array();
        $colcount=0;
        while (is_array($row = $res->fetch_assoc())) {
            if (!$colcount) {
                $colcount=sizeof($row);
            }
            $rows[] = array_change_key_case($row, CASE_LOWER);
        }
        if ($colcount==2) {
             $rows=array_reduce($rows, function ($acc, $rec) {
                [
                    $key,
                    $val,
                ]=array_values($rec);
                 $acc[$key]=$val;
                return $acc;
             }, []);
        }
        $res->close();
        return $rows;
    }
}
