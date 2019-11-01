<?
use Bitrix\Main\Loader,
    Bitrix\Astroblgaz\AdressTable,
    Bitrix\Astroblgaz\CustomerTable,
    Bitrix\Main\Application;

class customerFilter extends CBitrixComponent
{
    private $_items;
    private $_customers;
    private $_filter;

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
        $strFilter = array();
        $filter = array();

        foreach ($requestFilter as $item){
            if(is_int($item)){
                $filter[] = $item;
            }
            else{
                $strFilter[] = $item;
            }
        }

        // get chields
        foreach ($this->getItems() as $item){
            if(in_array($item["PARENT"], $requestFilter)){
                $filter[] = $item["ID"];
            }
        }
        // get chields include parents
        foreach ($this->getItems() as $item){
            if(in_array($item["PARENT"], $filter)){
                $filter[] = $item["ID"];
            }
        }

        $filter = array_unique($filter);
        $filter = array_merge($strFilter, $filter);

        $this->_filter = $filter;
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
     */
    public function setCustomers()
    {
        $customers = array();
        $result = CustomerTable::getList();
        while ($customer = $result->fetch()){
            $customers[$customer["ID"]] = $customer;
        }
        $this->_customers = $customers;
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

        $result = AdressTable::getList();
        while ($item = $result->fetch()){
            $items[$item["ID"]] = $item;
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

    public function implementRequest(){
        if(!empty($this->getItems())){
            $request = Application::getInstance()->getContext()->getRequest();
            if($request->isPost()){
                $filter = array();

                if(is_array($request->getPost($this->arParams["INPUT_NAME"]))){
                    foreach ($request->getPost($this->arParams["INPUT_NAME"]) as $value){

                        if(intval($value) > 0 && strlen($value) < 5){
                            if(!empty($this->_items[$value])){
                                $filter[] = $value;
                                $this->_items[$value]["selected"] = "selected";
                            }
                        }else{
                            if(!empty($value))
                            {
                                foreach ($this->getCustomers() as $item){

                                    switch ($value){
                                        case $item["ADRESS"]:
                                                $filter[] = $value;
                                                $this->arResult["CUSTOMERS_QUERY"] = $value;
                                            break;
                                        case $item["SCORE"]:
                                            $filter[] = $value;
                                            $this->arResult["SCORE_QUERY"] = $value;
                                            break;
                                    }

                                }
                            }
                        }
                    }
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
    }

    public function executeComponent()
    {

        if($this->startResultCache()){
            if(Loader::includeModule("trionix.astroblgaz")){
                $this->setItems();
                $this->setCustomers();
            }
            $this->endResultCache();
        }

        $this->implementRequest();

        $this->arResult["ITEMS"] = $this->getItems();
        $this->arResult["CUSTOMERS"] = $this->getCustomers();

        if(!empty($this->getItems())) {
            $this->grouppingItems();
        }

        $this->arResult["GROUP_ITEMS"] = $this->getItems();

        $this->includeComponentTemplate();

        return $this->getFilter();
    }
}