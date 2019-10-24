<?
/**
 * обновление каталога
 */

$arResult = array(
    "DOCUMENT_ROOT" => "/home/bitrix/ext_www/astroblgaz.ru",
    "IMPORT_FILES_DIR" => "/about/trionix/import/files/"
);

/*
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
AddMessage2Log(array(
    "TITLE" => "Catalog loader",
    "FILE" => __FILE__,
    "SERVER" => $_SERVER
));
*/
# if(empty($_SERVER["UNIQUE_ID"]))
#    die;

set_time_limit(1000); /* --- увеличим время выполнения скрипта --- */

$content = file_get_contents('php://input');

$uploaddir = $arResult["DOCUMENT_ROOT"].$arResult["IMPORT_FILES_DIR"];
#$file_compl = $uploaddir."catalog.txt";
$file_compl = $uploaddir."catalog_".time()."_".date("d_m_Y").".xml";

$fp = fopen($file_compl, "w");
fwrite($fp, $content);
fclose($fp);

?>