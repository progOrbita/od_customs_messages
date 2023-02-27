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
    private function buildFieldsValues(): void
    {
        $langs = AdminController::getLanguages();
        foreach ($langs as $id => $attr) {
            $this->fields_values[$attr['id_lang']] = ['translate' => $attr['name'], 'msg' => Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_', (int) $attr['id_lang'], null, null, '')];
        }
    }

    public function initContent()
    {
        parent::initContent();
        $post = $this->postProcessForm();
        $this->context->smarty->assign([
            'content' => $post . $this->displayForm()
        ]);
    }

    /**
     * function to display helperForm
     * 
     * @return string
     */
    public function displayForm(): string
    {
        $zones = $this->getOptionsCheckBox('zone');
        $countries = $this->getOptionsCheckBox('country');
        $states = $this->getOptionsCheckBox('state');

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
                        // 'autoload_rte' => true, // No funciona al utilizarse <p>
                        'lang' => true,
                        'row' => 100,
                    ],
                    $this->getCheckBoxField($this->module->l('Zones'), '_OD_SEND_CUSTOMS_MESSAGES_ZONES_', $zones),
                    $this->getCheckBoxField($this->module->l('Countries'), '_OD_SEND_CUSTOMS_MESSAGES_COUNTRIES_', $countries),
                    $this->getCheckBoxField($this->module->l('States'), '_OD_SEND_CUSTOMS_MESSAGES_STATES_', $states)
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
     * Get fields values of helper form of configuration
     * 
     * @param string label
     * @param string name
     * @param array data
     * 
     * @return array
     */
    private function getCheckBoxField(string $label, string $name, array $options): array
    {
        return [
            'type' => 'checkbox',
            'label'   => $label,
            'name' => $name,
            'values' => [
                'query' => $options,
                'id' => 'id',
                'name' => 'name'
            ],
            'expand' => [
                'print_total' => count($options),
                'default' => 'show',
                'show' => ['text' => $this->module->l('show'), 'icon' => 'plus-sign-alt'],
                'hide' => ['text' => $this->module->l('hide'), 'icon' => 'minus-sign-alt']
            ]
        ];
    }

    /**
     * Get fields values of helper form of configuration
     * 
     * @return array
     */
    private function getFieldsValues(): array
    {
        $data = [];
        foreach ($this->fields_values as $key => $value) {
            $data[$key] = Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_', (int) $key, null, null, '');
        }

        $result['_OD_SEND_CUSTOMS_MESSAGES_'] = $data;
        $this->getFieldsCheckBoxValue('ZONES', $result);
        $this->getFieldsCheckBoxValue('STATES', $result);
        $this->getFieldsCheckBoxValue('COUNTRIES', $result);

        return $result;
    }

    /**
     * function to get values of checkbox form 
     * 
     * @param string 'ZONES'|'STATES'|'COUNTRIES'
     * 
     * @return array
     */
    private function getFieldsCheckBoxValue(string $param, &$field_values): array
    {
        $data = explode(',', Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_' . $param . '_', null, null, null, ''));
        $res = [];
        foreach ($data as $key) {
            $res['_OD_SEND_CUSTOMS_MESSAGES_' . $param . '__' . $key] = true;
            $field_values['_OD_SEND_CUSTOMS_MESSAGES_' . $param . '__' . $key] = true;
        }

        return $res;
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
            $postDataVal = Tools::getValue('_OD_SEND_CUSTOMS_MESSAGES__' . $key);
            if ($this->fields_values[$key]['msg'] ==  $postDataVal) {
                continue;
            }

            $postData[$key] =  $postDataVal;
            $this->fields_values[$key]['msg'] = $postData[$key];
        }

        if (!Configuration::updateValue('_OD_SEND_CUSTOMS_MESSAGES_', $postData)) {
            return $this->module->displayError($this->module->l('Error al actualizar los mensajes'));
        }

        if (!$this->updateCheckBoxValue('ZONES', '_OD_SEND_CUSTOMS_MESSAGES_ZONES_')) {
            return $this->module->displayError($this->module->l('Error al actualizar las zonas'));
        }

        if (!$this->updateCheckBoxValue('STATES', '_OD_SEND_CUSTOMS_MESSAGES_STATES_')) {
            return $this->module->displayError($this->module->l('Error al actualizar los estados'));
        }

        if (!$this->updateCheckBoxValue('COUNTRIES', '_OD_SEND_CUSTOMS_MESSAGES_COUNTRIES_')) {
            return $this->module->displayError($this->module->l('Error al actualizar los paises'));
        }

        return $this->module->displayConfirmation($this->module->l('Actualización correcta'));
    }

    /**
     * function to get check box values of $_GET
     * 
     * @param string 'ZONES'|'STATES'|'COUNTRIES'
     * @param string $key
     * 
     * @return string
     */
    private function updateCheckBoxValue(string $param, string $key): string
    {
        $data = [];
        foreach (Tools::getAllValues() as $key => $value) {
            if (strpos($key, '_OD_SEND_CUSTOMS_MESSAGES_' . $param . '__') === false) {
                continue;
            }

            $len = strlen('_OD_SEND_CUSTOMS_MESSAGES_' . $param . '__');
            $data[] = substr($key, $len);
        }

        return Configuration::updateValue($key, implode(',', $data));
    }

    /**
     * Get checkbox options
     * 
     * @param string
     * 
     * @return array
     */
    public function getOptionsCheckBox(string $param): array
    {
        $res = [];
        $data = [];

        switch ($param) {
            case 'zone':
                $data = Zone::getZones(true);
                $key = 'id_zone';
                break;
            case 'state':
                $data = State::getStatesByIdCountry(6, true);
                $key = 'id_state';
                break;
            case 'country':
                $data = Country::getCountries($this->context->language->id, true);
                $key = 'id_country';
                break;
        }

        foreach ($data as $key => $value) {
            $res[] = ['id' => $value[$key], 'name' => $value['name']];
        }

        return $res;
    }
}
