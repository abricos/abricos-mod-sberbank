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
     * @var SberbankApp
     */
    public $app;

    public function __construct($app){
        $this->app = $app;
    }

    /**
     * @param PaymentsForm $form
     * @return mixed
     */
    public function Register($form){
        $config = $this->app->Config();
        $query = http_build_query(array(
            "orderNumber" => $form->order->id,
            "amount" => $form->order->total * 100,
            "returnUrl" => $form->urlReturnOk,
            "failUrl" => $form->urlReturnNo,
            "userName" => $config->login,
            "password" => $config->password
        ));
        $content = @$this->GetSSLPage("https://3dsec.sberbank.ru/payment/rest/register.do?".$query);
        if (!$content){
            $this->app->LogError('SberbankAPI->Register() can not receive data');
            return null;
        }

        $resp = json_decode($content);
        return $resp;
    }

    /**
     * @param PaymentsOrder $order
     * @param string $sberOrderId
     * @return mixed
     */
    function GetOrderStatus($sberOrderId){
        $config = $this->app->Config();

        $query = http_build_query(
            array(
                "orderId" => $sberOrderId,
                "userName" => $config->login,
                "password" => $config->password
            )
        );
        $content = @$this->GetSSLPage("https://3dsec.sberbank.ru/payment/rest/getOrderStatus.do?".$query);
        $resp = json_decode($content);
        return $resp;
    }

    public function GetSSLPage($url){
        if (!function_exists('curl_init')){
            return file_get_contents($url);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}