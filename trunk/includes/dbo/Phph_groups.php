<?php
/**
 * Table Definition for phph_groups
 */
require_once 'DB/DataObject.php';

class DBO_Phph_groups extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_groups';                     // table name
    public $group_id;                        // int(11)  not_null primary_key unique_key auto_increment
    public $group_name;                      // string(128)  not_null unique_key
    public $group_description;               // blob(65535)  blob
    public $group_created;                   // int(11)  not_null
    public $group_creator;                   // int(11)  
    public $group_level;                     // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_groups',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
