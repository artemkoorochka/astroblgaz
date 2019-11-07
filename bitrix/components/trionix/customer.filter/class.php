<?
use Bitrix\Main\Loader,
    Bitrix\Astroblgaz\AdressTable,
    Bitrix\Astroblgaz\CustomerTable,
    Bitrix\Main\Application;

class customerFilter extends CBitrixComponent
{
    private $_module = "trionix.astroblgaz";
    private $_fields = array();

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param mixed $errors
     */
    public function setError($error)
    {
        $this->_errors[] = $error;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->_filter;
    }

    /**
     * Set filter fields from map
     */
    public function setFields()
    {
        if(Loader::includeModule($this->_module)){

            $this->_filter["ADRESS"] = array();
            $adress = AdressTable::getList(array(
                "order" => array("LEVEL" => "ASC"),
                "group" => array("TITLE"),
                "select" => array("TITLE")
            ));
            while ($adresy = $adress->fetch()){
                $this->_filter["ADRESS"][]  = $adresy["TITLE"];
            }
            $this->_filter["ADRESS"] = array_unique($this->_filter["ADRESS"]);
            $this->_filter["CUSTOMER"] = CustomerTable::getMap();
        }
    }

    public function userPost(){
        $request = Application::getInstance()->getContext()->getRequest();
        if($request->isPost()){
            // set adress
            $this->arResult["POST"] = $_POST;
        }
    }

    public function combinateResultFilter(){
        $arFilter = array();

        foreach ($this->arResult["POST"][$this->arParams["INPUT_CUSTOMER"]] as $key=>$value){
            if(!empty($value)){
                $arFilter[$key] = $value;
            }
        }

        // PARENT
        if(Loader::includeModule($this->_module)){
            foreach ($this->arResult["POST"][$this->arParams["INPUT_ADRESS"]] as $value) {
                if(!empty($value)){
                    $value = explode(". ", $value);
                    unset($value[0]);
                    $value = implode(" ", $value);
                    if(!empty($value)){
                        $adress[] = $value;
                    }
                }
            }
            $adress = AdressTable::getList(array(
                "filter" => array("NAME" => $adress),
                "select" => array("*")
            ));
            while ($adresy = $adress->fetch()){

                $arFilter["PARENT"][] = $adresy["ID"];
            }

        }

        return $arFilter;
    }

    public function executeComponent()
    {
        if($this->arParams["USE_FILTER"] == "Y"){
            $this->setFields();
            $this->arResult["FIELDS"] = $this->getFields();
        }
        $this->userPost();
        $this->includeComponentTemplate();

        // Возможные варианты фильра
        if(!empty($this->arResult["POST"][$this->arParams["INPUT_CUSTOMER"]]["ADRESS"])){
            return array("ADRESS" => $this->arResult["POST"][$this->arParams["INPUT_CUSTOMER"]]["ADRESS"]);
        }
        elseif(!empty($this->arResult["POST"][$this->arParams["INPUT_CUSTOMER"]]["SCORE"])){
            return array("SCORE" => $this->arResult["POST"][$this->arParams["INPUT_CUSTOMER"]]["SCORE"]);
        }
        elseif(!empty($this->arResult["POST"][$this->arParams["INPUT_ADRESS"]])){
            return $this->combinateResultFilter();
        }
    }
}