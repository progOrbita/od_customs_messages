<?php

class AdminConfigMessagesController extends ModuleAdminController
{
    private $fields_values = [];

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->buildFieldsValues();
    }

    /**
     * build array fields_values with all languages of our db
     * 
     * @return void
     */
    private function buildFieldsValues()
    {
        $langs = AdminController::getLanguages();
        foreach ($langs as $id => $attr) {
            $this->fields_values[$attr['id_lang']] = ['translate' => $attr['name'], 'msg' => Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_', (int) $attr['id_lang'], null, null, '')];
        }
    }

    public function displayForm()
    {
        $form = [[
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Show shipping message')
                ],
                'input' => [
                    [
                        'type' => 'textarea',
                        'label' => $this->module->l('Message:'),
                        'name' => '_OD_SEND_CUSTOMS_MESSAGES_',
                        'autoload_rte' => true,
                        'lang' => true,
                        'row' => 100,
                    ]
                ],
                'submit' => [
                    'title' => $this->module->l('Guardar'),
                    'class' => 'btn btn-default pull-right'
                ],
            ],
        ]];

        $helper = new HelperForm();
        $helper->default_form_language = 1;
        $helper->languages = AdminController::getLanguages();
        $helper->token = $this->token;
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->module->name]);
        $helper->submit_action = 'submit' . $this->module->name;
        $helper->fields_value = $this->getFieldsValues();

        return $helper->generateForm($form);
    }

    /**
     * Post process
     * 
     * @return string
     */
    public function postProcessForm(): string
    {
        if (!Tools::isSubmit('submit' . $this->module->name)) {
            return '';
        }

        return $this->updateFieldsValue();
    }

    /**
     * Update fields value
     * 
     * @return string error|confirmation
     */
    public function updateFieldsValue(): string
    {
        $postData = [];
        foreach ($this->fields_values as $key => $value) {
            if (Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_', $key) == Tools::getValue('_OD_SEND_CUSTOMS_MESSAGES__' . $key)) {
                continue;
            }

            $postData[$key] = Tools::getValue('_OD_SEND_CUSTOMS_MESSAGES__' . $key);
            $this->fields_values[$key]['msg'] = $postData[$key];
        }

        if (!Configuration::updateValue('_OD_SEND_CUSTOMS_MESSAGES_', $postData)) {
            return $this->module->displayError($this->module->l('Error al actualizar los datos'));
        }

        return $this->module->displayConfirmation($this->module->l('Actualización hecha correctamente'));
    }
}
