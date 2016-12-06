<?php
/**
 * @package Abricos
 * @subpackage Sberbank
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isUpdate('0.1.0')){
    Abricos::GetModule('sberbank')->permission->Install();

    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."sberbank (
		  orderid VARCHAR(32) NOT NULL DEFAULT '' COMMENT '',
		  sberOrderId VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Идентификатор заказа в сберанке',
		  UNIQUE KEY orderid (orderid)
		 )".$charset
    );
}
