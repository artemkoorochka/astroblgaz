<?php
use Bitrix\Main\EventManager,
    Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

class trionix_astroblgaz extends CModule
{
    var $MODULE_ID = 'trionix.astroblgaz';
    var $PARTNER_NAME = 'Artem Trionix';
    var $PARTNER_URI = 'http://trionix.com';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = Loc::getMessage('TRIONIX_ASTROBLGAZ_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('TRIONIX_ASTROBLGAZ_DESC');
        $this->PARTNER_NAME = Loc::getMessage('TRIONIX_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('TRIONIX_PARTNER_URL');
    }

    /**
     * Register module
     * Install DB
     */
    function DoInstall()
    {
        $this->InstallFiles();
        $this->InstallDB();
        RegisterModule($this->MODULE_ID);
    }

    /**
     * Unregister module
     * Uninstall DB
     */
    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
    }

    /**
     * Run query
     * @return bool
     */
    function InstallDB()
    {
        global $DB;
        $DB->RunSQLBatch(dirname(__FILE__)."/sql/install.sql");
        return true;
    }

    /**
     * Run query
     * @return bool|void
     */
    function UnInstallDB()
    {
        global $DB;
        $DB->RunSQLBatch(dirname(__FILE__)."/sql/uninstall.sql");

        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles(dirname(__FILE__)."/components/trionix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/trionix", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles(dirname(__FILE__)."/components/trionix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/trionix");
        return true;
    }

}