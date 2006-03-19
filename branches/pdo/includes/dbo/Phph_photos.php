<?php
/**
 * Table Definition for phph_photos
 */
require_once 'DB/DataObject.php';

class DBO_Phph_photos extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_photos';                     // table name
    public $photo_id;                        // int(11)  not_null primary_key unique_key auto_increment
    public $user_id;                         // int(11)  not_null
    public $photo_title;                     // string(255)  
    public $photo_description;               // blob(65535)  blob
    public $photo_added;                     // int(11)  not_null
    public $photo_approved;                  // int(11)  
    public $photo_approved_by;               // int(11)  
    public $photo_width;                     // int(11)  
    public $photo_height;                    // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_photos',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
