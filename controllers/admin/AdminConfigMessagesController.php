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
                        // 'autoload_rte' => true,
                        'lang' => true,
                        'row' => 100,
                    ],
                    [
                        'type' => 'checkbox',
                        'label'   => $this->module->l('Zones'),
                        'name' => '_OD_SEND_CUSTOMS_MESSAGES_ZONES_',
                        'values' => [
                            'query' => $this->getOptionsCheckBox('zone'),
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'expand' => [
                            'print_total' => count($this->getOptionsCheckBox('zone')),
                            'default' => 'show',
                            'show' => array('text' => $this->module->l('show'), 'icon' => 'plus-sign-alt'),
                            'hide' => array('text' => $this->module->l('hide'), 'icon' => 'minus-sign-alt')
                        ]
                    ],
                    [
                        'type' => 'checkbox',
                        'label'   => $this->module->l('Countries'),
                        'name' => '_OD_SEND_CUSTOMS_MESSAGES_COUNTRIES_',
                        'values' => [
                            'query' => $this->getOptionsCheckBox('country'),
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'expand' => [
                            'print_total' => count($this->getOptionsCheckBox('country')),
                            'default' => 'show',
                            'show' => array('text' => $this->module->l('show'), 'icon' => 'plus-sign-alt'),
                            'hide' => array('text' => $this->module->l('hide'), 'icon' => 'minus-sign-alt')
                        ]
                    ],
                    [
                        'type' => 'checkbox',
                        'label'   => $this->module->l('States'),
                        'name' => '_OD_SEND_CUSTOMS_MESSAGES_STATES_',
                        'values' => [
                            'query' => $this->getOptionsCheckBox('state'),
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'expand' => [
                            'print_total' => count($this->getOptionsCheckBox('state')),
                            'default' => 'show',
                            'show' => array('text' => $this->module->l('show'), 'icon' => 'plus-sign-alt'),
                            'hide' => array('text' => $this->module->l('hide'), 'icon' => 'minus-sign-alt')
                        ]
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
     * Get fields values of helper form of configuration
     * 
     * @return array
     */
    private function getFieldsValues(): array
    {
        foreach ($this->fields_values as $key => $value) {
            $data[$key] = Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_', (int) $key, null, null, '');
        }

        $result['_OD_SEND_CUSTOMS_MESSAGES_'] = $data;
        foreach ($this->getFieldsCheckBoxValue('ZONES') as $key => $value) {
            $result[$key] = $value;
        }

        foreach ($this->getFieldsCheckBoxValue('STATES') as $key => $value) {
            $result[$key] = $value;
        }

        foreach ($this->getFieldsCheckBoxValue('COUNTRIES') as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * function to get values of checkbox form 
     * 
     * @param string 'ZONES'|'STATES'|'COUNTRIES'
     * 
     * @return array
     */
    private function getFieldsCheckBoxValue(string $param): array
    {
        $data = Configuration::get('_OD_SEND_CUSTOMS_MESSAGES_' . $param . '_');
        $data = explode(',', $data);
        $res = [];
        foreach ($data as $key) {
            $res['_OD_SEND_CUSTOMS_MESSAGES_' . $param . '__' . $key] = true;
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

        if (!Configuration::updateValue('_OD_SEND_CUSTOMS_MESSAGES_', $postData, true)) {
            return $this->module->displayError($this->module->l('Error al actualizar los datos'));
        }

        $zone = $this->updateCheckBoxValue('ZONES');
        if (!Configuration::updateValue('_OD_SEND_CUSTOMS_MESSAGES_ZONES_', $zone)) {
            return $this->module->displayError($this->module->l('Error al actualizar los datos'));
        }

        $states = $this->updateCheckBoxValue('STATES');
        if (!Configuration::updateValue('_OD_SEND_CUSTOMS_MESSAGES_STATES_', $states)) {
            return $this->module->displayError($this->module->l('Error al actualizar los datos'));
        }

        $countries = $this->updateCheckBoxValue('COUNTRIES');
        if (!Configuration::updateValue('_OD_SEND_CUSTOMS_MESSAGES_COUNTRIES_', $countries)) {
            return $this->module->displayError($this->module->l('Error al actualizar los datos'));
        }

        return $this->module->displayConfirmation($this->module->l('Actualización hecha correctamente'));
    }

    /**
     * function to get check box values of $_GET
     * 
     * @param string 'ZONES'|'STATES'|'COUNTRIES'
     * 
     * @return string
     */
    private function updateCheckBoxValue(string $param): string
    {
        $dataGet = Tools::getAllValues();
        $data = '';
        foreach ($dataGet as $key => $value) {
            if (strpos($key, '_OD_SEND_CUSTOMS_MESSAGES_' . $param . '__') === false) {
                continue;
            }

            $len = strlen('_OD_SEND_CUSTOMS_MESSAGES_' . $param . '__');
            $key = substr($key, $len);
            $data .= $key . ',';
        }

        $data = substr($data, 0, -1);
        return $data;
    }

    /**
     * create an array for options of the checkbox
     * 
     * @param string
     * 
     * @return array
     */
    public function getOptionsCheckBox(string $param): array
    {
        $res = [];
        switch ($param) {
            case 'zone':
                $zone = (Zone::getZones(true));
                foreach ($zone as $key => $value) {
                    $res[] = ['id' => $value['id_zone'], 'name' => $value['name']];
                }

                break;
            case 'state':
                $state = (State::getStatesByIdCountry(6));
                foreach ($state as $key => $value) {
                    $res[] = ['id' => $value['id_state'], 'name' => $value['name']];
                }

                break;
            case 'country':
                $country = (Country::getCountries($this->context->language->id, true));
                foreach ($country as $key => $value) {
                    if ($value['id_country'] == 6) {
                        continue;
                    }

                    $res[] = ['id' => $value['id_country'], 'name' => $value['name']];
                }

                break;
        }

        return $res;
    }
}
