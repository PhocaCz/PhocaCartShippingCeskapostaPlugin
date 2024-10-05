<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');
//jimport('joomla.log.log');
//JLog::addLogger( array('text_file' => 'com_phocacart_error_log.php'), JLog::ALL, array('com_phocacart'));
//phocacartimport('phocacart.utils.log');

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php')) {
    // Joomla 5 and newer
    require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php');
} else {
    // Joomla 4
    JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');
}

class plgPCSShipping_Ceskaposta extends CMSPlugin
{
    protected $name = 'shipping_ceskaposta';

    function __construct(&$subject, $config) {
        parent:: __construct($subject, $config);
        $this->loadLanguage();
    }
    /*
    function onPCSbeforeProceedToShipping(&$proceed, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        $proceed = 1;
        return true;
    }

    function onPCSbeforeSetShippingForm(&$form, $paramsC, $params, $order, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        return true;
    }

    function onPCSbeforeCheckShipping($pid, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        return true;
    }

    */


    /* Export Shipping Branch Info */

    function onPCSexportShippingBranchInfo($context, $orderIds, $shippingInfo, $eventData) {


        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }


        $paramsMethod = $shippingInfo->params;                                                                 // parameters of shipping method - get current parameters from current shipping method options

        $registry = new Registry;
        $registry->loadString($paramsMethod);
        $paramsMethod = $registry;


        /*
         * PARAMSMETHOD - parameters stored in shipping method (can be changed when user saves the shipping method)
         * PARAMSSHIPPING - parameters stored in order (column params_shipping) - they are stored in order so they don't change
         * ADDITIONALPARAMETERS - parameters which were changed by user in orders view: Example: weight - if user changed weigt by some order
         */

        /* Specific parameters set by users in orders view*/
        $additionalParameters = [];
        //$additionalParameters['adultContent'] 	= Factory::getApplication()->input->get( 'adultContent', array()); // set in parameters as default, can be set in orders view per form field
        $additionalParameters['default_weight'] = Factory::getApplication()->input->get('default_weight', ''); // set as default
        $additionalParameters['totalWeight']    = Factory::getApplication()->input->get('totalWeight', '');    // set in parameters as default, can be set in orders view per form field
        $additionalParameters['totalPay']       = Factory::getApplication()->input->get('totalPay', '');

        $o = '';
        if (!empty($orderIds)) {


            $db = Factory::getDBO();


            $wheres   = array();
            $wheres[] = 'o.id IN (' . implode(',', array_values($orderIds)) . ')';

            $query = ' SELECT DISTINCT o.*,'
                . ' os.title AS status_title,'
                . ' t.amount AS total_amount,'
                . ' s.id AS shippingid, s.title AS shippingtitle, s.tracking_link as shippingtrackinglink, s.tracking_description as shippingtrackingdescription, os.orders_view_display as ordersviewdisplay,'

                . ' us0.name_first as us0_name_first, us0.name_last as us0_name_last, us0.company as us0_company, us0.email as us0_email, us0.phone_1 as us0_phone_1, us0.phone_2 as us0_phone_2, us0.phone_mobile as us0_phone_mobile,'

                . ' us1.name_first as us1_name_first, us1.name_last as us1_name_last, us1.company as us1_company, us1.email as us1_email, us1.phone_1 as us1_phone_1, us1.phone_2 as us1_phone_2, us1.phone_mobile as us1_phone_mobile'


                . ' FROM #__phocacart_orders AS o'
                . ' LEFT JOIN #__phocacart_order_statuses AS os ON os.id = o.status_id'
                . ' LEFT JOIN #__phocacart_order_total AS t ON o.id = t.order_id AND t.type = \'brutto\''
                . ' LEFT JOIN #__phocacart_shipping_methods AS s ON s.id = o.shipping_id'
                . ' LEFT JOIN #__phocacart_order_users AS us0 ON o.id=us0.order_id AND us0.type = 0'
                . ' LEFT JOIN #__phocacart_order_users AS us1 ON o.id=us1.order_id AND us1.type = 1'
                . ' WHERE ' . implode(' AND ', $wheres)
                . ' GROUP BY o.id'
                . ' ORDER BY o.id';

            $db->setQuery($query);
            $orders = $db->loadObjectList();

            $path = PluginHelper::getLayoutPath('pcs', 'shipping_ceskaposta', 'default_branchinfo_export');

            // Render the pagenav
            ob_start();
            include $path;
            $o = ob_get_clean();


        }

        return $o;

    }

    /* Order edit view - administration */
    function onPCSgetShippingBranchInfoAdminEdit($pid, $item, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        $output = array();
        //$output['content'] = '';

        return $output;
    }

    /* Order list view - administration */
    function onPCSgetShippingBranchInfoAdminList($pid, $item, $shippingInfo, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }


        $paramsShipping = json_decode($item->params_shipping, true);// stored parameters of shipping method in order
        $paramsMethod   = $shippingInfo->params;                    // parameters of shipping method - get current parameters from current shipping method options

        $registry = new Registry;
        $registry->loadString($paramsMethod);
        $paramsMethod = $registry;


        // Get the path for the layout file
        $path = PluginHelper::getLayoutPath('pcs', 'shipping_ceskaposta', 'default_branchinfo_admin_list');

        // Render the pagenav
        ob_start();
        include $path;
        $o = ob_get_clean();

        $output            = array();
        $output['content'] = $o;

        return $output;
    }


    /* Render button for selecting branch points */
    function onPCSgetShippingBranches($context, &$item, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        $document = Factory::getDocument();
        $app      = Factory::getApplication();

        $registry = new Registry;
        $registry->loadString($item->params);
        $item->params = $registry;

        $pC = PhocacartUtils::getComponentParameters();

        // Be aware this instance of plugin can be used for more shipping options, this is why we use id array
        // because each shipping option can have different parameters

        $id      = (int)$item->id;
        $oParams = array();
        //$oParams['apiKey']	= '';// Not needed, don't display it in Frontend Javascript $item->params->get('api_key', '');
        //$oParams['type'] = $item->params->get('type', 'BALIKOVNY');
        $oParams[$id]['fields']                = $this->getBranchFields();
        $oParams[$id]['validate_pickup_point'] = $item->params->get('validate_pickup_point', 1);
        $oParams[$id]['display_opening_hours'] = $item->params->get('display_opening_hours', 0);
        $oParams[$id]['display_branch_photo']  = $item->params->get('display_branch_photo', 0);
        $oParams[$id]['theme']                 = $pC->get('theme', 'bs5');;

        $oLang = array(
            'MONDAY' => Text::_('MONDAY'),
            'TUESDAY' => Text::_('TUESDAY'),
            'WEDNESDAY' => Text::_('WEDNESDAY'),
            'THURSDAY' => Text::_('THURSDAY'),
            'FRIDAY' => Text::_('FRIDAY'),
            'SATURDAY' => Text::_('SATURDAY'),
            'SUNDAY' => Text::_('SUNDAY'),
            'PLG_PCS_SHIPPING_CESKAPOSTA_NONE' => Text::_('PLG_PCS_SHIPPING_CESKAPOSTA_NONE'),
            'PLG_PCS_SHIPPING_CESKAPOSTA_ERROR_PLEASE_SELECT_PICK_UP_POINT' => Text::_('PLG_PCS_SHIPPING_CESKAPOSTA_ERROR_PLEASE_SELECT_PICK_UP_POINT'));

        $document->addScriptOptions('phParamsPlgPcsCeskaposta', $oParams);
        $document->addScriptOptions('phLangPlgPcsCeskaposta', $oLang);

        $wa = $app->getDocument()->getWebAssetManager();
        $wa->useScript('core')->registerAndUseScript('plg_pcs_shipping_ceskaposta.ceskaposta-int', 'media/plg_pcs_shipping_ceskaposta/js/ceskaposta.js', array('version' => 'auto'));
        $wa->registerAndUseStyle('plg_pcs_shipping_ceskaposta.ceskaposta-css', 'media/plg_pcs_shipping_ceskaposta/css/ceskaposta.css', array('version' => 'auto'));

        //$document->addScriptOptions('phLangPC', $oLang);
        //$document->addScriptOptions('phVarsPC', $oVars);

        // Get the path for the layout file
        $path = PluginHelper::getLayoutPath('pcs', 'shipping_ceskaposta', 'default_selectpoint');

        // Render the pagenav
        ob_start();
        include $path;
        $o = ob_get_clean();


        $output            = array();
        $output['content'] = $o;

        return $output;

    }

    /* Check all input branch form fields to protect database from saving wrong values */
    function onPCScheckShippingBranchFormFields($context, &$items, $shippingMethod, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }


        // Allowed fields + filter values
        $fields = $this->getBranchFields();

        if (!empty($items)) {
            foreach ($items as $k => $v) {

                // Remove not allowed fields
                if (!in_array($k, $fields)) {
                    unset($items[$k]);
                } else {
                    if ($k == 'opening_hours') {
                        $items[$k] = PhocacartText::filterValue($v, 'text-div');
                    } else {
                        $items[$k] = PhocacartText::filterValue($v, 'text');
                    }

                }

            }
        }

        // ----
        /* When we save information we can add additional parameters set by shipping method parameters */
        /* We will ask live parameters by the method directly, not save it*/
        /*if (isset($shippingMethod->params)) {
            $registry = new Registry;
            $registry->loadString($shippingMethod->params);
            $params = $registry;

            $items['adultContent'] = $params->get('adult_content', 0);
            $items['default_weight'] = $params->get('default_weight', '');
            $items['zero_payment'] = $params->get('zero_payment', array());
        }*/
        // ----

        return true;

    }

    function onPCSgetShippingBranchInfo($context, $shippingMethod, $params, $eventData) {

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        $document = Factory::getDocument();
        $app      = Factory::getApplication();
        $wa       = $app->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('plg_pcs_shipping_ceskaposta.ceskaposta-css', 'media/plg_pcs_shipping_ceskaposta/css/ceskaposta.css', array('version' => 'auto'));

        $paramsMethod                          = [];
        $paramsMethod['display_opening_hours'] = $shippingMethod['params']->get('display_opening_hours', 0);


        // Get the path for the layout file
        $path = PluginHelper::getLayoutPath('pcs', 'shipping_ceskaposta', 'default_branchinfo');

        // Render the pagenav
        ob_start();
        include $path;
        $o = ob_get_clean();

        $output            = array();
        $output['content'] = $o;

        return $output;
    }





    /*
    function onPCSbeforeShowPossibleShippingMethod(&$active, $params, $eventData){

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        // Shipping plugin can disable/deactivate current shipping method in possible shipping method list based on own rules
        // $active = false;

        return true;

    }

    function onPCSonInfoViewDisplayContent($data, $eventData){

        if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
            return false;
        }

        $output = array();
        $output['content'] = '';

        return $output;

    }
    */


    /*
     * Which Branch info will be stored to order, to not load all information from external server
    */
    function getBranchFields() {

        return array('id', 'name', 'municipality_name', 'municipality_district_name', 'address', 'zip', 'country', 'thumbnail', 'description', 'opening_hours', 'type', 'subtype');

    }

}

?>
