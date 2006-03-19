<?php
/**
 * Table Definition for phph_user_ip
 */
require_once 'DB/DataObject.php';

class DBO_Phph_user_ip extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_user_ip';                    // table name
    public $user_id;                         // int(11)  not_null primary_key
    public $ip;                              // string(8)  not_null primary_key
    public $last_visit;                      // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_user_ip',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
