<?
/**
 * Vendor Trionix
 * based on Bitrix Framework
 * Povered by Artem Koorochka
 * @subpackage koorochka.astroblgaz
 * @copyright 2019 Trionix
 */

use Bitrix\Main\Loader,
    Bitrix\Astroblgaz\CustomerTable,
    Bitrix\Main\Web\Json;

class customerList extends CBitrixComponent{

    /**
     * @return mixed|void
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        if(Loader::includeModule("trionix.astroblgaz")){
            if($this->arParams["GET_MAP"] == "Y"){
                $this->arResult["MAP"] = CustomerTable::getMap();
            }
            $query = array(
                "select" => array("*")
            );
            if($this->arParams["LIMIT"] > 0){
                $query["limit"] = $this->arParams["LIMIT"];
            }
            if(!empty($this->arParams["SHOW_FIELDS"])){
                $query["select"] = $this->arParams["SHOW_FIELDS"];
            }
            if(!empty($this->arParams["FILTER"])){
                $query["filter"] = $this->arParams["FILTER"];
            }

            ///////////////////// test PARENT RUNTIME
            if(!empty($query["filter"]["PARENT"])){
                unset($query["filter"]["PARENT"]);

                $query["runtime"] = array(
                    new Bitrix\Main\Entity\ReferenceField(
                        "PARENT",
                        "\Bitrix\Astroblgaz\AdressTable",
                        array(
                            "=this.PARENT" => "ref.ID"
                        ),
                        array(
                            "join_type" => "inner"
                        )
                    )
                );

            }


            //////////////////// end test PARENT
            $customers = CustomerTable::getList($query);
            while ($customer = $customers->fetch())
            {
                $this->arResult["ITEMS"][] = $customer;
            }
        }

        if($this->arParams["MODE"] == "JSON"){
            foreach ($this->arResult["ITEMS"] as $option){
                $options[] = $option["ADRESS"];
            }
            if(!empty($options)){
                $options = array("options" => $options);
                echo Json::encode($options);
            }
        }else{
            $this->includeComponentTemplate();
        }
    }

}
?>