<?php
/**
 * @package Abricos
 * @subpackage Sberbank
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class SberbankQuery
 */
class SberbankQuery {

    public static function OrderAppend(AbricosApplication $app, $orderid, $sberOrderId){
        $db = $app->db;
        $sql = "
            INSERT INTO ".$db->prefix."sberbank
            (orderid, sberOrderId) VALUES (
                '".bkstr($orderid)."',
                '".bkstr($sberOrderId)."'
            )
        ";
        $db->query_write($sql);
    }

    public static function Order(AbricosApplication $app, $orderid){
        $db = $app->db;
        $sql = "
            SELECT *
            FROM ".$db->prefix."sberbank
            WHERE orderid='".bkstr($orderid)."'
            LIMIT 1
        ";
        return $db->query_first($sql);
    }
}
