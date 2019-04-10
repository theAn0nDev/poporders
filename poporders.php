<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require('class/LastsOrders.php');
require('class/StateOrder.php');

class poporders extends Module
{
    public function __construct()
    {
        $this->name = 'poporders';
        $this->tab = 'front_office_features';
        $this->version = '0.1';
        $this->author = 'Letellier Kevin';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Pop Orders');
        $this->description = $this->l('Popup des dernieres commandes effectuées, tous clients confondu');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('POP_ORDERS_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    public function hookDisplayHeader($params)
    {

        $lastsOrders = new LastsOrders();
        $order = $lastsOrders->getOrderFormated($lastsOrders->getOneLastOrder());


        $this->context->controller->addCSS($this->_path.'css/poporders.css', 'all');

        $this->context->smarty->assign(
            array(
                'my_module_name' => Configuration::get('POP_ORDERS_NAME'),
                'my_module_link' => $this->context->link->getModuleLink('poporders', 'display'),
                'order' => $order,
                'path' => $this->_path
            )
        );
        return $this->display(__FILE__, 'poporders.tpl');
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('header') &&
            Configuration::updateValue('POP_ORDERS_NAME', 'Pop Orders') &&
            Configuration::updateValue('POP_ORDERS_DAYS_DELAY', '7') &&
            Configuration::updateValue('POP_ORDERS_ORDER_STATE_ACCEPTED', '2,3,4,5,12') &&
            Configuration::updateValue('POP_ORDERS_DELAY_REFRESH', '10000');
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('POP_ORDERS_NAME') ||
            !Configuration::deleteByName('POP_ORDERS_DAYS_DELAY') ||
            !Configuration::deleteByName('POP_ORDERS_ORDER_STATE_ACCEPTED') ||
            !Configuration::deleteByName('POP_ORDERS_DELAY_REFRESH')
        ) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $errors = false;
        $output = null;

        if (Tools::isSubmit('submit'.$this->name))
        {
            // Get default Language
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            $stateOrder = new StateOrder($default_lang);

            // true si c'est un tableau
            $fields = [
                'POP_ORDERS_NAME' => false,
                'POP_ORDERS_DAYS_DELAY' => false,
                'POP_ORDERS_ORDER_STATE_ACCEPTED' => true,
                'POP_ORDERS_DELAY_REFRESH' => false
            ];

            foreach($fields as $field => $isArray){

                $var_field = '';

                if ($isArray) {

                    $countState = $stateOrder->getCountAllStatesDb();

                    $format = $field.'_';

                    for ($i = 1; $i <= $countState; $i++) {

                        if (Tools::getValue($format.$i)) {

                            if ($var_field != '') {
                                $var_field .= ',';
                            }

                            $var_field .= $i;
                        }
                    }

                    $var_field = ($var_field == '') ? '0' : $var_field;

                    Configuration::updateValue($field, $var_field);

                } else {

                    $var_field = strval(Tools::getValue($field));

                    if (!$var_field  || empty($var_field))
                        $errors = true;
                    else
                    {
                        Configuration::updateValue($field, $var_field);
                    }
                }


            }

            if ($errors) {

                $output .= $this->displayError( $this->l('Invalid '.$var_field) );
            } else {

                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default Language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $stateOrder = new StateOrder($default_lang);

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Nom du module'),
                    'name' => 'POP_ORDERS_NAME',
                    'size' => 20,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Délai de recherche (en jours)'),
                    'name' => 'POP_ORDERS_DAYS_DELAY',
                    'size' => 4,
                    'required' => true
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Etats de commandes acceptés dans le popup'),
                    'name' => 'POP_ORDERS_ORDER_STATE_ACCEPTED',
                    'required' => true,
                    'values' => array(
                        'query' => $choiceState = $stateOrder->getValuesCheckboxBackOff(),
                        'id' => 'check_id',
                        'name' => 'name',
                        'desc' => $this->l('Please select')
                    ),
                    'expand' => array(
                        'default' => 'show',
                        'show' => array('text' => $this->l('Afficher'), 'icon' => 'plus-sign-alt'),
                        'hide' => array('text' => $this->l('Masquer'), 'icon' => 'minus-sign-alt'),
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Delai de reload du popup (en milisecondes, un minimum de 5000 est conseillé)'),
                    'name' => 'POP_ORDERS_DELAY_REFRESH',
                    'size' => 10,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['POP_ORDERS_NAME'] = Configuration::get('POP_ORDERS_NAME');
        $helper->fields_value['POP_ORDERS_DAYS_DELAY'] = Configuration::get('POP_ORDERS_DAYS_DELAY');
        $helper->fields_value['POP_ORDERS_DELAY_REFRESH'] = Configuration::get('POP_ORDERS_DELAY_REFRESH');

        for ($i = 1; $i <= $stateOrder->getCountAllStatesDb(); $i++) {

            if (in_array($i, explode(',', Configuration::get('POP_ORDERS_ORDER_STATE_ACCEPTED')))) {

                $helper->fields_value['POP_ORDERS_ORDER_STATE_ACCEPTED_'.$i] = true;
            }
        }

        return $helper->generateForm($fields_form);
    }

}
