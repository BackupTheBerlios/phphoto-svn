<?php
/**
 * Table Definition for phph_config
 */
require_once 'DB/DataObject.php';

class DBO_Phph_config extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_config';                     // table name
    public $config_name;                     // string(255)  not_null primary_key unique_key
    public $config_value;                    // blob(65535)  blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_config',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
