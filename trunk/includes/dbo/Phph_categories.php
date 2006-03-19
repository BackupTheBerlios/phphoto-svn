<?php
/**
 * Table Definition for phph_categories
 */
require_once 'DB/DataObject.php';

class DBO_Phph_categories extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_categories';                 // table name
    public $category_id;                     // int(11)  not_null primary_key unique_key auto_increment
    public $category_parent;                 // int(11)  
    public $category_name;                   // string(128)  not_null
    public $category_description;            // blob(65535)  blob
    public $category_created;                // int(11)  not_null
    public $category_creator;                // int(11)  
    public $category_order;                  // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_categories',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
