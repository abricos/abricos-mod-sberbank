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
 */
class SberbankManager extends Ab_ModuleManager {

    public function IsAdminRole(){
        return $this->IsRoleEnable(SberbankAction::ADMIN);
    }

    public function IsWriteRole(){
        if ($this->IsAdminRole()){
            return true;
        }
        return $this->IsRoleEnable(SberbankAction::WRITE);
    }

    public function IsViewRole(){
        if ($this->IsWriteRole()){
            return true;
        }
        return $this->IsRoleEnable(SberbankAction::VIEW);
    }

    public function GetApp(){
        Abricos::GetApp('payments');
        return parent::GetApp();
    }

    public function AJAX($d) {
        return $this->GetApp()->AJAX($d);
    }

    public function Bos_MenuData(){
        if (!$this->IsAdminRole()){
            return null;
        }
        $i18n = $this->module->I18n();
        return array(
            array(
                "name" => "sberbank",
                "title" => $i18n->Translate('title'),
                "icon" => "/modules/sberbank/images/cp_icon.gif",
                "url" => "sberbank/wspace/ws",
                "parent" => "controlPanel"
            )
        );
    }
}
