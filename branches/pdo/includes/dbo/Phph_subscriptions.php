<?php
/**
 * Table Definition for phph_subscriptions
 */
require_once 'DB/DataObject.php';

class DBO_Phph_subscriptions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_subscriptions';              // table name
    public $category_id;                     // int(11)  not_null primary_key
    public $user_id;                         // int(11)  not_null primary_key
    public $subscription_date;               // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_subscriptions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
