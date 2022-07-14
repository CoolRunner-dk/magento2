<?php
namespace CoolRunner\Shipping\Block;

use CoolRunner\Shipping\Helper\Data as Helper;
use Magento\Framework\View\Element\Template;
/**
 *  GoogleMaps
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    11.08.2020
 * Time:    11:23
 */

class GoogleMaps extends Template implements
    \Magento\Framework\DataObject\IdentityInterface{

    const CACHE_TAG = 'cr_google_maps';

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getBlockId()];
    }

    /**
     * @return string
     */
    public function getGoogleMapsKey(): string {
        return $this->_scopeConfig->getValue('cr_settings/droppoints/google_maps_key')?:'';
    }

    /**
     * @return bool
     */
    public function isMapActivated(){
        return (boolean) $this->_scopeConfig->getValue('cr_settings/droppoints/enable_map');
    }

    /**
     * @return array
     */
    public function getCarriersIcon(){
        return json_encode([
            Helper::COOLRUNNER_SERVICE_PREFIX.'dao'       => $this->getViewFileUrl('CoolRunner_Shipping/images/icons/dao.png'),
            Helper::COOLRUNNER_SERVICE_PREFIX.'bring'     => $this->getViewFileUrl('CoolRunner_Shipping/images/icons/bring.png'),
//            Helper::COOLRUNNER_SERVICE_PREFIX.'gls'       => $this->getViewFileUrl('CoolRunner_Shipping/images/icons/gls.png'),
//            Helper::COOLRUNNER_SERVICE_PREFIX.'postnord'  => $this->getViewFileUrl('CoolRunner_Shipping/images/icons/postnord.png'),
//            Helper::COOLRUNNER_SERVICE_PREFIX.'coolrunner'=> $this->getViewFileUrl('CoolRunner_Shipping/images/icons/coolrunner.png'),
//            Helper::COOLRUNNER_SERVICE_PREFIX.'posti'     => $this->getViewFileUrl('CoolRunner_Shipping/images/icons/posti.png'),
//            Helper::COOLRUNNER_SERVICE_PREFIX.'dhl'       => $this->getViewFileUrl('CoolRunner_Shipping/images/icons/dhl.png'),
//            Helper::COOLRUNNER_SERVICE_PREFIX.'homerunner'=> $this->getViewFileUrl('CoolRunner_Shipping/images/icons/homerunner.png'),
        ]);
    }
}
