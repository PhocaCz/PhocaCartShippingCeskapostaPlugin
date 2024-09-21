<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die;
// When selecting point, make the checkbox active
$idCheckbox = 'phshippingopt'.$item->id;
$s          = PhocacartRenderStyle::getStyles();
$t          = [];
$layoutAAQ	= new FileLayout('popup_container_iframe', null, array('component' => 'com_phocacart'));
$type       = $item->params->get('type', 'BALIKOVNY');

?>
<div class="ph-checkout-shipping-additional-info ph-checkout-ceskaposta-box">
    <a href="https://b2c.cpost.cz/locations/?type=<?php echo $type ?>" data-bs-target="#phPcsCeskaPostaPopup" data-bs-toggle="modal" data-id="phPcsCeskaPostaPopup" data-src="https://b2c.cpost.cz/locations/?type=<?php echo $type ?>" class="ph-btn btn ph-btn-ceskaposta-select-pickup-point phModalContainerCommonIframeButton" role="button" title="<?php echo Text::_('PLG_PCS_SHIPPING_CESKAPOSTA_SELECT_PICK_UP_POINT'); ?>"  data-bs-toggle="tooltip" data-placement="top" onclick="phSetCheckboxActive('<?php echo $idCheckbox ?>')"><?php echo Text::_('PLG_PCS_SHIPPING_CESKAPOSTA_SELECT_PICK_UP_POINT'); ?></a>
    <input type="hidden" id="ceskaposta-checkbox-id-<?php echo $item->id ?>" value="<?php echo $idCheckbox ?>" >
    <div class="ph-checkout-ceskaposta-info-container"><div class="ph-checkout-ceskaposta-info-label"><?php echo Text::_('PLG_PCS_SHIPPING_CESKAPOSTA_SELECTED_POINT'); ?>:</div> <div class="ph-checkout-shipping-info-box ph-checkout-ceskaposta-info-box" id="ceskaposta-point-info-<?php echo $item->id ?>"><?php echo Text::_('PLG_PCS_SHIPPING_CESKAPOSTA_NONE'); ?></div><?php
    if (!empty($oParams['fields'])) {
        foreach($oParams['fields'] as $k => $v) {
            echo '<input type="hidden" id="ceskaposta-field-'.$v.'-'.$item->id.'" name="phshippingmethodfield['.$item->id.']['.$v.']" value="" />';
        }
    }
    ?></div>
</div><?php

PhocacartRenderJs::renderModalCommonIframeWindow(['allow_geolocation' => true]);
//echo '<div id="phContainer"></div>';
echo '<div id="phContainerPopupPcs'.$item->id .'" class="phContainerPopup">';
$d						= array();
$d['id']				= 'phPcsCeskaPostaPopup';
$d['title']				= Text::_('PLG_PCS_SHIPPING_CESKAPOSTA_SELECT_PICK_UP_POINT');
$d['icon']				= $s['i']['marker'];
$d['closebutton']       = 1;
$d['modal-class']       = 'modal-xl';
$d['t']					= $t;
$d['s']					= $s;
echo $layoutAAQ->render($d);
echo '</div>';// end phContainerPopup
?>

