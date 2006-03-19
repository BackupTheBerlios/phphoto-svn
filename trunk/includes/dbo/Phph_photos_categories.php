<?php
/**
 * Table Definition for phph_photos_categories
 */
require_once 'DB/DataObject.php';

class DBO_Phph_photos_categories extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_photos_categories';          // table name
    public $photo_id;                        // int(11)  not_null
    public $category_id;                     // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_photos_categories',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
