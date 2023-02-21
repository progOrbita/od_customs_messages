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

}
