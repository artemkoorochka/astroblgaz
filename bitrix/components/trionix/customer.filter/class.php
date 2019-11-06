<?
use Bitrix\Main\Loader,
    Bitrix\Astroblgaz\AdressTable,
    Bitrix\Astroblgaz\CustomerTable,
    Bitrix\Main\Application,
    Bitrix\Main\Localization\Loc;

class customerFilter extends CBitrixComponent
{
    private $_items;
    private $_itemParents = array();
    private $_customers;
    private $_filter = array();
    private $_tmpFilter = array();
    private $_errors;

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
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @param mixed $filter
     */
    public function setFilter($requestFilter)
    {
        $this->_filter = array_merge($this->_filter, $requestFilter);
        $this->_tmpFilter = array();

        // search and register parents
        foreach ($requestFilter as $id){
            if(is_array($this->_itemParents[$id])){
                $this->_tmpFilter = array_merge($this->_tmpFilter, $this->_itemParents[$id]);
            }
        }

        if(!empty($this->_tmpFilter)){
            $this->setFilter($this->_tmpFilter);
        }
    }

    /**
     * @return mixed
     */
    public function getCustomers()
    {
        return $this->_customers;
    }

    /**
     * Set customers from DB
     * @param $adresQuery
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function setCustomers($adresQuery)
    {
        $customers = array();
        $filter = array();

        if(empty($this->getFilter())){
            $result = CustomerTable::getList();
        }else{
            $result = CustomerTable::getList(array(
                "filter" => array("PARENT" => $this->getFilter())
            ));
        }

        while ($customer = $result->fetch()){

            if(!empty($this->arResult["CUSTOMERS_QUERY"]) || !empty($this->arResult["SCORE_QUERY"])){
                if($this->arResult["CUSTOMERS_QUERY"] == $customer["ADRESS"]){
                    $customers[$customer["ID"]] = $customer;
                    $filter[] = $customer["ID"];
                }

                if($this->arResult["SCORE_QUERY"] == $customer["SCORE"]){
                    $customers[$customer["ID"]] = $customer;
                    $filter[] = $customer["ID"];
                }

            }else{
                $filter[] = $customer["ID"];
                $customers[$customer["ID"]] = $customer;

            }
        }

        $this->_customers = $customers;

        if(!empty($adresQuery) && is_array($adresQuery)) {
            if(empty($this->arResult["CUSTOMERS_QUERY"]) && empty($this->arResult["SCORE_QUERY"])){
                //$this->_filter = array();
                $this->setError(Loc::getMessage("ADRESS_IS_EMPTY"));
            }
            $this->_filter = $filter;
        }
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Set items from DB
     */
    public function setItems()
    {
        $items = array();
        // get parent map
        foreach ($this->getItems() as $item){
            if($item["PARENT"] > 0)
                $parents[$item["PARENT"]][] = $item["ID"];
        }

        $result = AdressTable::getList();
        while ($item = $result->fetch()){
            $items[$item["ID"]] = $item;

            // fill perents map
            if($item["PARENT"] > 0){
                $this->_itemParents[$item["PARENT"]][] = $item["ID"];
            }
        }

        $this->_items = $items;
    }

    public function grouppingItems(){
        $currentTitle = "";
        $separatedItems = array();
        foreach ($this->getItems() as $id=>$arItem){
            $separatedItems[$arItem["TITLE"]][] = $arItem;
        }

        $this->_items = $separatedItems;
    }

    /**
     * @return \Bitrix\Main\HttpRequest
     * @throws \Bitrix\Main\SystemException
     */
    public function implementRequest(){
        $adresQuery = "";
        if(!empty($this->getItems())){
            $request = Application::getInstance()->getContext()->getRequest();
            if($request->isPost()){
                $adresQuery = $request->getPost($this->arParams["INPUT_NAME"]);
                $filter = array();
                if(is_array($request->getPost($this->arParams["INPUT_NAME"]))){
                    foreach ($request->getPost($this->arParams["INPUT_NAME"]) as $value){
                        foreach ($this->getItems() as $item){
                            if($value == $item["NAME"] . " " . $item["SHORT_NAME"] . "."){
                                $filter[] = $item["ID"];
                                $this->_items[$item["ID"]]["selected"] = "selected";
                            }
                        }
                    }

                    $this->arResult["CUSTOMERS_QUERY"] = trim($_POST[$this->arParams["INPUT_NAME"]]["ADRESS"]);
                    $this->arResult["SCORE_QUERY"] = trim($_POST[$this->arParams["INPUT_NAME"]]["SCORE"]);


                }else{
                    $search = $request->getPost($this->arParams["INPUT_NAME"]);
                    $this->arResult["SEARCH_QUERY"] = $search;
                    if(strpos($search, ",")){
                        foreach ($this->getCustomers() as $item){
                            if($item["ADRESS"] == $search){
                                $filter[] = $search;
                            }
                        }
                    }else{
                        $search = explode(".", $search);
                        $search[0] = trim($search[0]);
                        $search[1] = trim($search[1]);
                        foreach ($this->getItems() as $item){
                            if(
                                $item["SHORT_NAME"] == $search[0] &&
                                $item["NAME"] == $search[1]
                            ){
                                $filter[] = $item["ID"];
                            }
                        }
                    }
                }

                $this->setFilter($filter);
            }

            // set filter mode
            if($request->get("mode") == "full"){
                $this->arParams["MODE"] = "full";
            }
        }

        return $adresQuery;
    }

    public function executeComponent()
    {
        
        if($this->startResultCache()){
            if(Loader::includeModule("trionix.astroblgaz")){
                $this->setItems();
            }
            $this->endResultCache();
        }

        $adresQuery = $this->implementRequest();
        $this->setCustomers($adresQuery);

        $this->arResult["ITEMS"] = $this->getItems();
        $this->arResult["CUSTOMERS"] = $this->getCustomers();

        if(!empty($this->getItems())) {
            $this->grouppingItems();
        }

        $this->arResult["GROUP_ITEMS"] = $this->getItems();
        $this->arResult["ERRORS"] = $this->getErrors();

        $this->includeComponentTemplate();

        if(empty($this->getErrors())){
            return $this->getFilter();
        }
    }
}