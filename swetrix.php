<?php

/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class Swetrix extends Module
{
    protected $config_form = false;
    public  $name;
    public  $tab;
    public  $version;
    public  $author;
    public  $need_instance;
    public  $bootstrap;
    public  $displayName;
    public  $description;
    public  $ps_versions_compliancy;

    public function __construct()
    {
        $this->name = 'swetrix';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'panariga';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Swetrix Analytics');
        $this->description = $this->l('Swetrix Analytics');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {


        return parent::install() &&


            $this->registerHook('displaySearch') &&
            $this->registerHook('displayCustomerAccount') &&
            $this->registerHook('displayAddressSelectorBottom') &&
            $this->registerHook('displayPaymentTop') &&
            $this->registerHook('displayBeforeCarrier') &&
            $this->registerHook('displayShoppingCart') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayAfterBodyOpeningTag') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('displayAfterBodyOpeningTag') &&
            $this->registerHook('dashboardZoneTwo');
    }

    public function uninstall()
    {
        Configuration::deleteByName('swetrix_api_address');
        Configuration::deleteByName('swetrix_fe_address');
        Configuration::deleteByName('swetrix_project_id');
        Configuration::deleteByName('swetrix_password');
        return parent::uninstall();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submit_swetrix_config')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_swetrix_config';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => 'API Address',
                        'name' => 'swetrix_api_address',
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Front End Address',
                        'name' => 'swetrix_fe_address',
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Project ID',
                        'name' => 'swetrix_project_id',
                    ),

                    array(
                        'type' => 'text',
                        'label' => 'Password',
                        'name' => 'swetrix_password',
                    ),



                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'swetrix_api_address' => Configuration::get('swetrix_api_address', null, null, null,  'https://api.swetrix.com'),
            'swetrix_fe_address' => Configuration::get('swetrix_fe_address', null, null, null,  'https://swetrix.com'),
            'swetrix_project_id' => Configuration::get('swetrix_project_id'),
            'swetrix_password' => Configuration::get('swetrix_password'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {

            Configuration::updateValue($key, Tools::getValue($key));
        }
    }


    public function hookdisplayAfterBodyOpeningTag()
    {
        if (Configuration::get('swetrix_project_id', '') != '') {
            $this->smarty->assign(array(
                'swetrix_api_address' => Configuration::get('swetrix_api_address',  'https://api.swetrix.com'),
                'swetrix_project_id' => Configuration::get('swetrix_project_id'),
            ));
            return $this->display(__FILE__, 'views/templates/front/displayAfterBodyOpeningTag.tpl');
        }
    }

    public function hookdashboardZoneTwo()
    {
        if (Configuration::get('swetrix_fe_address') != '') {
            try {
                $this->smarty->assign(array(
                    'swetrix_fe_address' => Configuration::get('swetrix_fe_address',  'https://swetrix.com'),
                    'swetrix_project_id' => Configuration::get('swetrix_project_id'),
                    'swetrix_password' => Configuration::get('swetrix_password'),
                ));
                return $this->display(__FILE__, 'views/templates/admin/dashboardZoneTwo.tpl');
            } catch (Throwable $e) {
                error_log('Swetrix Exception: ' . $e->getMessage());
            }
        }
    }

    public function hookdisplayProductAdditionalInfo($params)
    {
        try {
            $this->smarty->assign(array(
                'swetrix_event_data' => [
                    'ev' => 'ProductView',
                    'unique' => false,
                    'meta' => [
                        'id_product' => (string) $params['product']->id,
                        'name' => (string)  $params['product']->name,
                    ],
                ]
            ));
            return $this->display(__FILE__, 'views/templates/front/event.tpl');
        } catch (Throwable $e) {
            error_log('Swetrix Exception: ' . $e->getMessage());
        }
    }
    public function hookdisplayOrderConfirmation($params)
    {
        try {
            $order = $params['order'];

            $this->smarty->assign(array(
                'swetrix_event_data' => [
                    'ev' => 'Purchase',
                    'unique' => false,
                    'meta' => [
                        'value' => (string)  $order->total_paid,
                        'transaction_id' => (string)  $order->id,
                    ],
                ]
            ));


            return $this->display(__FILE__, 'views/templates/front/event.tpl');
        } catch (Throwable $e) {
            error_log('Swetrix Exception: ' . $e->getMessage());
        }
    }

    public function hookdisplayShoppingCart()
    {

        $this->smarty->assign(array(
            'swetrix_event_data' => [
                'ev' => 'ShoppingCartView',
                'unique' => false,
                'meta' => [],
            ]
        ));
        return $this->display(__FILE__, 'views/templates/front/event.tpl');
    }

    public function hookdisplayBeforeCarrier()
    {
        $this->smarty->assign(array(
            'swetrix_event_data' => [
                'ev' => 'CarrierListView',
                'unique' => false,
                'meta' => [],
            ]
        ));
        return $this->display(__FILE__, 'views/templates/front/event.tpl');
    }

    public function hookdisplayPaymentTop()
    {

        $this->smarty->assign(array(
            'swetrix_event_data' => [
                'ev' => 'PaymentView',
                'unique' => false,
                'meta' => [],
            ]
        ));
        return $this->display(__FILE__, 'views/templates/front/event.tpl');
    }
    public function hookdisplayAddressSelectorBottom()
    {

        $this->smarty->assign(array(
            'swetrix_event_data' => [
                'ev' => 'AddressSelectorView',
                'unique' => false,
                'meta' => [],
            ]
        ));



        return $this->display(__FILE__, 'views/templates/front/event.tpl');
    }
    public function hookdisplayCustomerAccount()
    {

        $this->smarty->assign(array(
            'swetrix_event_data' => [
                'ev' => 'CustomerAccountView',
                'unique' => false,
                'meta' => [],
            ]
        ));


        return $this->display(__FILE__, 'views/templates/front/event.tpl');
    }
    public function hookdisplaySearch()
    {
        $this->smarty->assign(array(
            'swetrix_event_data' => [
                'ev' => 'SearchViewNotFound',
                'unique' => false,
                'meta' => [
                    'searchString' => (string) Tools::getValue('s'),
                ],
            ]
        ));

        return $this->display(__FILE__, 'views/templates/front/event.tpl');
    }
}
