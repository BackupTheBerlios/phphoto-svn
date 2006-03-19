<?php
/**
 * Table Definition for phph_sessions
 */
require_once 'DB/DataObject.php';

class DBO_Phph_sessions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_sessions';                   // table name
    public $session_id;                      // string(32)  not_null primary_key unique_key
    public $session_start;                   // int(11)  not_null
    public $session_time;                    // int(11)  not_null
    public $session_ip;                      // string(8)  not_null
    public $user_id;                         // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_sessions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
