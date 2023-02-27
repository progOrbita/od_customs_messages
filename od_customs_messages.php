<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Od_customs_messages extends Module
{
    public function __construct()
    {
        $this->name = 'od_customs_messages';
        $this->version = '1.0.0';
        $this->author = 'Jose Barreiro';
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.1',
            'max' => '1.7.9',
        ];

        parent::__construct();

        $this->displayName = $this->l('Customs messages module');
        $this->description = $this->l('Module to show message on shipping checkout.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install(): bool
    {
        return parent::install() && $this->registerHook('displayBeforeCarrier');
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && Configuration::deleteByName('_OD_SEND_CUSTOMS_MESSAGES_')
            && Configuration::deleteByName('_OD_SEND_CUSTOMS_MESSAGES_ZONES_')
            && Configuration::deleteByName('_OD_SEND_CUSTOMS_MESSAGES_STATES_')
            && Configuration::deleteByName('_OD_SEND_CUSTOMS_MESSAGES_COUNTRIES_');
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminConfigMessages'));
    }

    public function hookDisplayBeforeCarrier()
    {
        if(!$this->context->cart->id_address_delivery || !$this->context->cart->id_lang){
            return;
        }
        
        $addr = new Address($this->context->cart->id_address_delivery, $this->context->cart->id_lang);
        if ($this->validateAddress('ZONES', $addr) || $this->validateAddress('COUNTRIES', $addr) || $this->validateAddress('STATES', $addr)) {
            $this->context->smarty->assign([
                'msg' => Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_', $this->context->cart->id_lang)
            ]);

            return $this->display(__FILE__, 'od_customs_messages.tpl');
        }
    }

    /**
     * function to validate fields of address
     * 
     * @param string $param ZONES|STATES|COUNTRIES
     * @param Address $addr Address
     * 
     * @return bool
     */
    private function validateAddress(string $param, Address $addr): bool
    {
        $datas = explode(',', Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_' . $param . '_'));
        foreach ($datas as $data) {
            switch ($param) {
                case 'ZONES':
                    if ($data == $addr->getZoneById($addr->id)) {
                        return true;
                    }
                    break;
                case 'COUNTRIES':
                    if ($data == $addr->id_country) {
                        return true;
                    }
                    break;
                case 'STATES':
                    if ($data == $addr->id_state) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}
