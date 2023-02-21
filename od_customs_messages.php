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

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionFrontControllerSetMedia');
    }

}
