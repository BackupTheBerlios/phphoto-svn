<?php
/**
 * Table Definition for phph_permissions
 */
require_once 'DB/DataObject.php';

class DBO_Phph_permissions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_permissions';                // table name
    public $user_id;                         // int(11)  
    public $group_id;                        // int(11)  
    public $permission;                      // string(128)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_permissions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
