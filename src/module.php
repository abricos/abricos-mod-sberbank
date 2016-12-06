<?php
/**
 * @package Abricos
 * @subpackage Sberbank
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class SberbankModule
 */
class SberbankModule extends Ab_Module {

    public function __construct(){
        $this->version = "0.1.0";
        $this->name = "sberbank";

        $this->permission = new SberbankPermission($this);
    }

    public function Bos_IsMenu(){
        return true;
    }
}

class SberbankAction {
    const VIEW = 10;
    const WRITE = 30;
    const ADMIN = 50;
}

class SberbankPermission extends Ab_UserPermission {

    public function __construct(SberbankModule $module){
        $defRoles = array(
            new Ab_UserRole(SberbankAction::VIEW, Ab_UserGroup::GUEST),
            new Ab_UserRole(SberbankAction::VIEW, Ab_UserGroup::REGISTERED),
            new Ab_UserRole(SberbankAction::VIEW, Ab_UserGroup::ADMIN),

            new Ab_UserRole(SberbankAction::WRITE, Ab_UserGroup::ADMIN),

            new Ab_UserRole(SberbankAction::ADMIN, Ab_UserGroup::ADMIN)
        );
        parent::__construct($module, $defRoles);
    }

    public function GetRoles(){
        return array(
            SberbankAction::VIEW => $this->CheckAction(SberbankAction::VIEW),
            SberbankAction::WRITE => $this->CheckAction(SberbankAction::WRITE),
            SberbankAction::ADMIN => $this->CheckAction(SberbankAction::ADMIN)
        );
    }
}

Abricos::ModuleRegister(new SberbankModule());
