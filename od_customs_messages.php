<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Od_customs_messages extends Module
{
    public function __construct()
    {
        $this->name = 'od_customs_messages';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Jose Barreiro';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.1',
            'max' => '1.7.9',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Customs messages module');
        $this->description = $this->l('Module to show message on shipping checkout.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install():bool
    {
        return parent::install()
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayBeforeCarrier');
    }

    public function uninstall():bool
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

    {
        $addr = new Address($this->context->cart->id_address_delivery, $this->context->cart->id_lang);

                'msg' => Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_', $this->context->cart->id_lang)
            ]);
        }
    }
}
