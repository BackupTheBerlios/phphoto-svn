<?php
foreach ($_SERVER as $key => $val) {
        echo "$key => $val <br />";
}
echo realpath($HTTP_SERVER_VARS['SCRIPT_FILENAME']);

$cfg['wrapper_path'] = 'test.php';
$cfg['tmp_pAG_path'] = 'phpAutoGallery/';


        if (isset($HTTP_SERVER_VARS['SCRIPT_URL']) && $HTTP_SERVER_VARS['SCRIPT_URL'] != $HTTP_SERVER_VARS['SCRIPT_NAME']) {
                // CGI
                $filesystem_root_path = str_replace($HTTP_SERVER_VARS['SCRIPT_URL'], "/", $HTTP_SERVER_VARS['SCRIPT_FILENAME']);
        }
        else {
                // APACHE
                $filesystem_root_path = str_replace($HTTP_SERVER_VARS['SCRIPT_NAME'], "/", $HTTP_SERVER_VARS['SCRIPT_FILENAME']);
        }
        $filesystem_pAG_path_abs = str_replace($cfg['wrapper_path'], '', str_replace("\\", "/", realpath($HTTP_SERVER_VARS['SCRIPT_FILENAME'])));
        $filesystem_pAG_path_rel = '/' . str_replace($filesystem_root_path, '', $filesystem_pAG_path_abs);
        $web_pAG_path_abs = $HTTP_SERVER_VARS['SERVER_NAME'] . $filesystem_pAG_path_rel;
        $web_pAG_path_rel = $filesystem_pAG_path_rel;

echo $filesystem_pAG_path_abs . "<br />";
echo $filesystem_pAG_path_rel . "<br />";
echo $web_pAG_path_abs . "<br />";
echo $web_pAG_path_rel . "<br />";

?>

