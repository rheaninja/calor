<?php
namespace Magento\Quote\Api\Data;

/**
 * Extension class for @see \Magento\Quote\Api\Data\AddressInterface
 */
class AddressExtension extends \Magento\Framework\Api\AbstractSimpleObject implements AddressExtensionInterface
{
    /**
     * @return string|null
     */
    public function getPickupLocationCode()
    {
        return $this->_get('pickup_location_code');
    }

    /**
     * @param string $pickupLocationCode
     * @return $this
     */
    public function setPickupLocationCode($pickupLocationCode)
    {
        $this->setData('pickup_location_code', $pickupLocationCode);
        return $this;
    }

    /**
     * @return \Magento\SalesRule\Api\Data\RuleDiscountInterface[]|null
     */
    public function getDiscounts()
    {
        return $this->_get('discounts');
    }

    /**
     * @param \Magento\SalesRule\Api\Data\RuleDiscountInterface[] $discounts
     * @return $this
     */
    public function setDiscounts($discounts)
    {
        $this->setData('discounts', $discounts);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMpDeliveryDate()
    {
        return $this->_get('mp_delivery_date');
    }

    /**
     * @param string $mpDeliveryDate
     * @return $this
     */
    public function setMpDeliveryDate($mpDeliveryDate)
    {
        $this->setData('mp_delivery_date', $mpDeliveryDate);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMpDeliveryTime()
    {
        return $this->_get('mp_delivery_time');
    }

    /**
     * @param string $mpDeliveryTime
     * @return $this
     */
    public function setMpDeliveryTime($mpDeliveryTime)
    {
        $this->setData('mp_delivery_time', $mpDeliveryTime);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMpHouseSecurityCode()
    {
        return $this->_get('mp_house_security_code');
    }

    /**
     * @param string $mpHouseSecurityCode
     * @return $this
     */
    public function setMpHouseSecurityCode($mpHouseSecurityCode)
    {
        $this->setData('mp_house_security_code', $mpHouseSecurityCode);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMpDeliveryComment()
    {
        return $this->_get('mp_delivery_comment');
    }

    /**
     * @param string $mpDeliveryComment
     * @return $this
     */
    public function setMpDeliveryComment($mpDeliveryComment)
    {
        $this->setData('mp_delivery_comment', $mpDeliveryComment);
        return $this;
    }
}
