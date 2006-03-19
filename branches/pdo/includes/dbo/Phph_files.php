<?php
/**
 * Table Definition for phph_files
 */
require_once 'DB/DataObject.php';

class DBO_Phph_files extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_files';                      // table name
    public $file_id;                         // int(11)  not_null primary_key unique_key auto_increment
    public $photo_id;                        // int(11)  not_null
    public $file_name;                       // string(255)  not_null unique_key
    public $file_created;                    // int(11)  not_null
    public $file_accessed;                   // int(11)  not_null
    public $file_width;                      // int(11)  
    public $file_height;                     // int(11)  
    public $file_original;                   // int(1)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_files',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
