<?php
/**
 * City table creation
 * Copyright (C) 2019
 *
 * This file is part of Dyson/SinglePageCheckout.
 *
 * Dyson/SinglePageCheckout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Dyson\SinglePageCheckout\Model\Data;

use Dyson\SinglePageCheckout\Api\Data\DysonCityInterface;

class DysonCity extends \Magento\Framework\Api\AbstractExtensibleObject implements DysonCityInterface
{

    /**
     * Get dyson_city_id
     * @return string|null
     */
    public function getDysonCityId()
    {
        return $this->_get(self::DYSON_CITY_ID);
    }

    /**
     * Set dyson_city_id
     * @param string $dysonCityId
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setDysonCityId($dysonCityId)
    {
        return $this->setData(self::DYSON_CITY_ID, $dysonCityId);
    }

    /**
     * Get region_id
     * @return string|null
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * Set region_id
     * @param string $regionId
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Dyson\SinglePageCheckout\Api\Data\DysonCityExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Dyson\SinglePageCheckout\Api\Data\DysonCityExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param string $storeId
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get city
     * @return string|null
     */
    public function getCity()
    {
        return $this->_get(self::CITY);
    }

    /**
     * Set city
     * @param string $city
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }
}
