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
    "PARENT_SECTION" => null,
    "SECTION_ID" => null,
    "LEVEL_SECTION_ID" => null,
    "ADRESS_MAP" => AdressTable::getMap(),
    "CUSTOMER_MAP" => CustomerTable::getMap(),
    "FIELDS" => array(),
    "DB" => null,
    "TREE" => array(

    ),
    "NODES" => array()
);




while ($reader->read()) {
    if ($reader->nodeType == XMLReader::ELEMENT) {
        if($reader->attributeCount == $arParams["MAX_ATTRIBUTES"]){




            ///////////
            foreach ($arResult["ADRESS_MAP"] as $code=>$field){
                $arResult["FIELDS"][$code] = null;
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
            $arResult["FIELDS"]["SORT"] = 0;
            // parent section condition
            // TODO set level
            // TODO set section_ID clear sort field
            ////////////// fill tree
            $arResult["NODES"][] = $arResult["FIELDS"];
        }
    }
}

// Feel tree
foreach ($arResult["NODES"] as $key=>$node)
{
    if(!is_set($arResult["TREE"][$node["TITLE"]])){
        $arResult["TREE"][$node["TITLE"]] = array("LEVEL" => 0, "ID" => 0);
    }

    if($key > 0){
        $arResult["NODES"][$key]["PREVIOS_NODE"] = $arResult["NODES"][$key-1]["TITLE"];
        if($node["TITLE"] != $arResult["NODES"][$key]["PREVIOS_NODE"]){
            $arResult["NODES"][$key]["LEVEL"] = $arResult["NODES"][$key-1]["LEVEL"] + 1;
            // set level
            if($arResult["TREE"][$node["TITLE"]]["LEVEL"] == 0){
                $arResult["TREE"][$node["TITLE"]]["LEVEL"] = $arResult["NODES"][$key]["LEVEL"];
            }
        }
    }

    // TODO set sort field as sectionId from a tree
    $arResult["NODES"][$key]["SORT"] = $arResult["TREE"][$node["TITLE"]];

    if($arResult["PARENT_SECTION"] > 0){
        $arResult["NODES"][$key]["SORT"] = $arResult["PARENT_SECTION"];
    }


    $arResult["DB"] = AdressTable::add($arResult["NODES"][$key]);
    if($arResult["DB"]->isSuccess()){
        $arResult["PARENT_SECTION"] = $arResult["DB"]->getId();


        $arResult["TREE"][$node["TITLE"]]["ID"] = $arResult["PARENT_SECTION"];


    }
}



$sections = AdressTable::getList();
while ($section = $sections->fetch()){
    d($section);
}

die;

while ($reader->read()) {
    if ($reader->nodeType == XMLReader::ELEMENT) {
        if($reader->attributeCount > $arParams["MAX_ATTRIBUTES"]){
            d("It abbonent");
            d(array(
                utf8win1251($reader->name),
                utf8win1251($reader->getAttribute("Дом")),
                utf8win1251($reader->getAttribute("Корпус")),
                utf8win1251($reader->getAttribute("Строение")),
                utf8win1251($reader->getAttribute("Квартира")),
                utf8win1251($reader->getAttribute("Комната")),
                utf8win1251($reader->getAttribute("ГУИД")),
                utf8win1251($reader->getAttribute("АдресСтрокой")),
                utf8win1251($reader->getAttribute("ЛицевойСчет")),
                utf8win1251($reader->getAttribute("СуммаДолга")),
                utf8win1251($reader->getAttribute("ДатаФормированияДолга")),
                utf8win1251($reader->getAttribute("ДатаЗапланированногоТО"))
            ));
        }else{
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
            // parent section condition
            if($arResult["FIELDS"]["TITLE"] == utf8win1251("Улица")){

            }else{
                $arResult["FIELDS"]["SORT"] = $arResult["PARENT_SECTION"];
            }


            $arResult["DB"] = AdressTable::add($arResult["FIELDS"]);
            if($arResult["DB"]->isSuccess()){
                $arResult["PARENT_SECTION"] = $arResult["DB"]->getId();
            }
        }


    }
}

// </editor-fold>


$sections = AdressTable::getList();
while ($section = $sections->fetch()){
    d($section);
}