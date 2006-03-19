<?php
/**
 * Table Definition for phph_session_history
 */
require_once 'DB/DataObject.php';

class DBO_Phph_session_history extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_session_history';            // table name
    public $session_id;                      // string(32)  not_null primary_key unique_key
    public $session_start;                   // int(11)  not_null
    public $session_ip;                      // string(8)  not_null
    public $user_id;                         // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_session_history',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
