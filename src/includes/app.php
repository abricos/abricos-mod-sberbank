<?php
/**
 * @package Abricos
 * @subpackage Sberbank
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class SberbankManager
 *
 * @property SberbankManager $manager
 */
class SberbankApp extends PaymentsEngine {

    protected function GetClasses(){
        return array(
            'Config' => 'SberbankConfig'
        );
    }

    protected function GetStructures(){
        return 'Config';
    }

    public function ResponseToJSON($d){
        switch ($d->do){
            case "config":
                return $this->ConfigToJSON();
            case "configSave":
                return $this->ConfigSaveToJSON($d->config);

        }
        return null;
    }

    public function OwnerConfigData(){
        return array(
            'cronCheckOrderStatus' => true
        );
    }

    public function FormFill(PaymentsForm $form){
        $form->method = 'LINK';

        $sberAPI = new SberbankAPI($this);
        $result = $sberAPI->Register($form);

        if (!$result){
            $form->error = 1;
            return;
        }

        if (isset($result->errorCode) && $result->errorCode > 0){
            $this->LogError('Register Order: '.$result->errorMessage);
            $form->error = 2;
            return;
        }

        $form->url = $result->formUrl;

        if (isset($result->orderId) && !empty($result->orderId)){
            SberbankQuery::OrderAppend($this, $form->order->id, $result->orderId);
        }
    }

    public function API($action, $p1, $p2, $p3){
        return AbricosResponse::ERR_BAD_REQUEST;
    }

    public function OrderStatusUpdateByPOST(){
    }

    public function OrderStatusRequest(PaymentsOrder $order){
        $sberAPI = new SberbankAPI($this);

        $d = SberbankQuery::Order($this, $order->id);
        if (empty($d)){
            return;
        }

        $result = $sberAPI->GetOrderStatus($d['sberOrderId']);
        if (!isset($result->OrderStatus)){
            return;
        }
        switch (intval($result->OrderStatus)){
            case 2:
                return PaymentsEngine::STATUS_PAID;
        }
    }

    public function ConfigToJSON(){
        $res = $this->Config();
        return $this->ResultToJSON('config', $res);
    }

    /**
     * @return SberbankConfig|int
     */
    public function Config(){
        if ($this->CacheExists('Config')){
            return $this->Cache('Config');
        }

        if (!$this->manager->IsViewRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $phrases = Abricos::GetModule('sberbank')->GetPhrases();

        $d = array();
        for ($i = 0; $i < $phrases->Count(); $i++){
            $ph = $phrases->GetByIndex($i);
            $d[$ph->id] = $ph->value;
        }

        /** @var SberbankConfig $config */
        $config = $this->InstanceClass('Config', $d);

        if (empty($config->merchant)){
            $this->LogWarn('Merchant not set in Config');
        }

        if (empty($config->login)){
            $this->LogWarn('Merchant not set in Config');
        }

        if (empty($config->password)){
            $this->LogWarn('Password not set in Config');
        }

        $config->urlPay = "https://3dsec.sberbank.ru/payment/merchants/".$config->merchant."/payment_ru.html";
        $config->urlPayError = "https://3dsec.sberbank.ru/payment/merchants/".$config->merchant."/errors_ru.html";

        return $this->_cache['Config'] = $config;
    }

    public function ConfigSaveToJSON($d){
        $this->ConfigSave($d);
        return $this->ConfigToJSON();
    }

    public function ConfigSave($d){
        if (!$this->manager->IsAdminRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $utmf = Abricos::TextParser(true);

        /** @var SberbankConfig $config */
        $config = $this->InstanceClass('Config', $d);

        $phs = Abricos::GetModule('sberbank')->GetPhrases();
        $phs->Set("isRealMethod", $config->isRealMethod);
        $phs->Set("merchant", $utmf->Parser($config->merchant));
        $phs->Set("login", $utmf->Parser($config->login));
        $phs->Set("password", $utmf->Parser($config->password));

        Abricos::$phrases->Save();

        $this->CacheClear();
    }
}
