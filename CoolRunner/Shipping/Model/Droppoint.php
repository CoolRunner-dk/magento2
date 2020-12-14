<?php
/**
 *  Droppoint
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    12.08.2020
 * Time:    11:40
 */
namespace CoolRunner\Shipping\Model;

use CoolRunner\Shipping\Api\Data\DroppointInterface;
use Magento\Framework\DataObject;

/**
 * Class Droppoint
 * @method $this setId(int $id)
 * @method $this setName(string $name)
 * @method $this setDistance(int $distance)
 * @method $this setAddress(array $address)
 * @method $this setCoordinates(array $coordinates)
 * @method $this setOpeningHours(array $hours)
 * @method int getDistance()
 * @method array getAddress() Associative array with keys "street", "zip_code", "city", "country_code"
 *
 * @package CoolRunner\Shipping
 */
class Droppoint extends DataObject implements DroppointInterface {

    /**
     * @return int|void
     */
    public function getId() {
        return $this->_getData('id');
    }

    /**
     * Get concatenated string of location Name + Street + Post Code + City (Distance)
     *
     * @return string
     */
    public function getLocation() {
        $address = $this->getAddress();
        $format = $this->getDistance()?'%1,%2,%3 %4 (Distance: %5m)':'%1,%2,%3 %4';
        return __('%1,%2,%3 %4 (Distance: %5m)',$this->getName(),$address['street'],$address['zip_code'],$address['city'],$this->getDistance());
    }

    /**
     * Get concatenated string of address Street + Post Code + City (Distance)
     *
     * @return string
     */
    public function getAddressLocation() {
        $address = $this->getAddress();
        $format = $this->getDistance()?'%1,%2 %3 (Distance: %4m)':'%1,%2 %3';
        return __($format,$address['street'],$address['zip_code'],$address['city'],$this->getDistance());
    }

    /**
     * @return string
     */
    public function getStreet(){
        return isset($this->getAddress()['street'])?$this->getAddress()['street']:'';
    }

    /**
     * @return string
     */
    public function getZipCode(){
        return isset($this->getAddress()['zip_code'])?$this->getAddress()['zip_code']:'';
    }

    /**
     * @return string
     */
    public function getCity(){
        return isset($this->getAddress()['city'])?$this->getAddress()['city']:'';
    }

    /**
     * @return string
     */
    public function getCountryCode(){
        return isset($this->getAddress()['country_code'])?$this->getAddress()['country_code']:'';
    }

    /**
     * @return float
     */
    public function getLatitude() {
        return isset($this->_getData('coordinates')['latitude'])?$this->_getData('coordinates')['latitude']:0;
    }

    /**
     * @return float
     */
    public function getLongitude() {
        return isset($this->_getData('coordinates')['longitude'])?$this->_getData('coordinates')['longitude']:0;
    }

    /**
     * Associative array with keys "latitude","longitude"
     *
     * @return array
     */
    public function getCoordinates(){
        return $this->_getData('coordinates');
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->_getData('name');
    }

    /**
     * Associative array with keys "monday","tuesday",... Sub level with keys "from","to"
     * @return array
     */
    public function getOpeningHours(){
        return $this->_getData('opening_hours');
    }

    /**
     * @return string
     */
    public function getFormattedOpeningHours(){
        $html ='<ul>';

        foreach ($this->_getData('opening_hours') as $_day => $_hours){
            $html .= '<li>';
            $html .= sprintf('<strong class="day">%s:</strong> ',__(ucfirst($_day)));
            $html .= $_hours['from'] .' - '.$_hours['to'];
            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }
}
