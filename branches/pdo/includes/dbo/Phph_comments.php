<?php
/**
 * Table Definition for phph_comments
 */
require_once 'DB/DataObject.php';

class DBO_Phph_comments extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'phph_comments';                   // table name
    public $comment_id;                      // int(11)  not_null primary_key unique_key auto_increment
    public $photo_id;                        // int(11)  not_null
    public $user_id;                         // int(11)  not_null
    public $comment_title;                   // string(255)  
    public $comment_text;                    // blob(65535)  blob
    public $comment_date;                    // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DBO_Phph_comments',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
