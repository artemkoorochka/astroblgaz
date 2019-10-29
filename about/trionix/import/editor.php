<?
// <editor-fold defaultstate="collapsed" desc=" # Preparato prolog">
use Bitrix\Main\Loader,
    Bitrix\Astroblgaz\AdressTable,
    Bitrix\Astroblgaz\CustomerTable,
    Bitrix\Main\Type\Date;

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function d($value){
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc=" # Params for script">
$arParams = array(
    "MAX_ATTRIBUTES" => 3,
    "MODULE_NAME" => "trionix.astroblgaz",
    "MODULE_PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/",
    "SQL_INSTALL_BATCH" => "/install/sql/install.sql",
    "SQL_UNINSTALL_BATCH" => "/install/sql/uninstall.sql",
);

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc=" # Clear all data">
global $DB;
$DB->RunSQLBatch($arParams["MODULE_PATH"] . $arParams["MODULE_NAME"] . $arParams["SQL_UNINSTALL_BATCH"]);
$DB->RunSQLBatch($arParams["MODULE_PATH"] . $arParams["MODULE_NAME"] . $arParams["SQL_INSTALL_BATCH"]);
// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" # Read XML file">

$reader = new XMLReader();

if (!$reader->open("files/catalog_1572263850_28_10_2019.xml")) {
    die("Failed to open 'data.xml'");
}

if(!Loader::includeModule("trionix.astroblgaz")){
    die("Module '" . $arParams["MODULE_NAME"] . "' is not install");
}


$arResult = array(
    "ADRESS_MAP" => AdressTable::getMap(),
    "CUSTOMER_MAP" => CustomerTable::getMap(),
    "FIELDS" => array(),
    "DB" => null,
    "PARENT_TREE" => array()

);

while ($reader->read()) {
    if ($reader->nodeType == XMLReader::ELEMENT) {
        if($reader->attributeCount == $arParams["MAX_ATTRIBUTES"]){

            foreach ($arResult["ADRESS_MAP"] as $code=>$field){
                $arResult["FIELDS"][] = $code;
            }
            unset($arResult["FIELDS"]["ID"]);

            $arResult["FIELDS"]["ACTIVE"] = "Y";
            $arResult["FIELDS"]["LID"] = SITE_ID;
            $arResult["FIELDS"]["DATE_CREATE"] = new Date();
            $arResult["FIELDS"]["DATE_UPDATE"] = new Date();
            $arResult["FIELDS"]["TITLE"] = utf8win1251($reader->name);
            $arResult["FIELDS"]["NAME"] = utf8win1251($reader->getAttribute('Наименование'));
            $arResult["FIELDS"]["SHORT_NAME"] = utf8win1251($reader->getAttribute('Сокращение'));
            $arResult["FIELDS"]["XML_ID"] = utf8win1251($reader->getAttribute('ID_ФИАС'));
            $arResult["FIELDS"]["LEVEL"] = $reader->depth;
            $arResult["FIELDS"]["PARENT"] = $arResult["PARENT_TREE"][$reader->depth - 1];

            $arResult["DB"] = AdressTable::add($arResult["FIELDS"]);
            if($arResult["DB"]->isSuccess()){
                $arResult["PARENT_TREE"][$reader->depth] = $arResult["DB"]->getId();
            }

        }
        else{

            foreach ($arResult["CUSTOMER_MAP"] as $code=>$field){
                $arResult["FIELDS"][] = $code;
            }
            unset($arResult["FIELDS"]["ID"]);

            $arResult["FIELDS"]["ACTIVE"] = "Y";
            $arResult["FIELDS"]["LID"] = SITE_ID;
            $arResult["FIELDS"]["DATE_CREATE"] = new Date();
            $arResult["FIELDS"]["DATE_UPDATE"] = new Date();
            $arResult["FIELDS"]["TITLE"] = utf8win1251($reader->name);
            $arResult["FIELDS"]["HOUSE"] = utf8win1251($reader->getAttribute('Дом'));
            $arResult["FIELDS"]["CORPUSE"] = utf8win1251($reader->getAttribute('Корпус'));
            $arResult["FIELDS"]["BUILDING"] = utf8win1251($reader->getAttribute('Строение'));
            $arResult["FIELDS"]["FLAT"] = utf8win1251($reader->getAttribute('Квартира'));
            $arResult["FIELDS"]["ROOM"] = utf8win1251($reader->getAttribute('Комната'));
            $arResult["FIELDS"]["GUID"] = utf8win1251($reader->getAttribute('ГУИД'));
            $arResult["FIELDS"]["ADRESS"] = utf8win1251($reader->getAttribute('АдресСтрокой'));
            $arResult["FIELDS"]["SCORE"] = utf8win1251($reader->getAttribute('ЛицевойСчет'));
            $arResult["FIELDS"]["CREDIT"] = utf8win1251($reader->getAttribute('СуммаДолга'));
            //                 utf8win1251($reader->getAttribute("ДатаФормированияДолга")),
            //                utf8win1251($reader->getAttribute("ДатаЗапланированногоТО"))
            $arResult["FIELDS"]["PARENT"] = $arResult["PARENT_TREE"][$reader->depth-1];
            $arResult["DB"] = CustomerTable::add($arResult["FIELDS"]);
        }

    }
}

// </editor-fold>


$sections = CustomerTable::getList(array("select" => array("*")));
while ($section = $sections->fetch()){
    d($section);
}