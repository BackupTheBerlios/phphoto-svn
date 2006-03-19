<?php
/**
 * Table Definition for phph_users
 */
require_once 'DB/DataObject.php';

class DBO_Phph_users extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_users';                      // table name
    public $user_id;                         // int(11)  not_null primary_key unique_key auto_increment
    public $user_login;                      // string(128)  not_null unique_key
    public $user_pass;                       // string(32)  not_null
    public $user_name;                       // string(256)  
    public $user_title;                      // string(256)  
    public $user_email;                      // string(256)  
    public $user_jid;                        // string(256)  
    public $user_www;                        // string(256)  
    public $user_from;                       // string(256)  
    public $user_registered;                 // int(11)  
    public $user_lastlogin;                  // int(11)  
    public $user_language;                   // string(8)  
    public $user_admin_language;             // string(8)  
    public $user_level;                      // int(11)  not_null
    public $user_admin;                      // int(1)  not_null
    public $user_activation;                 // string(32)  
    public $user_activated;                  // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_users',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
