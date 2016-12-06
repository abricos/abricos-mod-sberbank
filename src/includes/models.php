<?php
/**
 * @package Abricos
 * @subpackage Sberbank
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */


/**
 * Class SberbankConfig
 *
 * @property bool $isRealMethod
 * @property string $merchant
 * @property string $login
 * @property string $password
 * @property string $urlPay
 * @property string $urlPayError
 * @property string $urlResult
 */
class SberbankConfig extends AbricosModel {
    protected $_structModule = 'sberbank';
    protected $_structName = 'Config';
}

class SberbankAPI {

    /**
     * @var SberbankConfig
     */
    public $config;

    public function __construct(SberbankConfig $config){
        $this->config = $config;
    }

    /**
     * @param PaymentsForm $form
     * @return mixed
     */
    public function Register($form){
        $query = http_build_query(array(
            "orderNumber" => $form->order->id,
            "amount" => $form->order->total * 100,
            "returnUrl" => $form->urlReturnOk,
            "failUrl" => $form->urlReturnNo,
            "userName" => $this->config->login,
            "password" => $this->config->password
        ));
        $resp = json_decode(file_get_contents("https://3dsec.sberbank.ru/payment/rest/register.do?".$query));
        return $resp;
    }

    /**
     * @param PaymentsOrder $order
     * @param string $sberOrderId
     * @return mixed
     */
    function GetOrderStatus($sberOrderId){
        $query = http_build_query(
            array(
                "orderId" => $sberOrderId,
                "userName" => $this->config->login,
                "password" => $this->config->password
            )
        );
        $resp = json_decode(file_get_contents("https://3dsec.sberbank.ru/payment/rest/getOrderStatus.do?".$query));
        return $resp;
    }
}