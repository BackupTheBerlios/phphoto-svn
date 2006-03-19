<?php
/**
 * Table Definition for phph_group_users
 */
require_once 'DB/DataObject.php';

class DBO_Phph_group_users extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_group_users';                // table name
    public $group_id;                        // int(11)  not_null primary_key
    public $user_id;                         // int(11)  not_null primary_key
    public $add_time;                        // int(11)  not_null
    public $added_by;                        // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_group_users',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
