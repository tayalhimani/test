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

namespace Dyson\SinglePageCheckout\Api\Data;

interface DysonCityInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const CITY = 'city';
    const DYSON_CITY_ID = 'entity_id';
    const STORE_ID = 'store_id';
    const REGION_ID = 'region_id';

    /**
     * Get dyson_city_id
     * @return string|null
     */
    public function getDysonCityId();

    /**
     * Set dyson_city_id
     * @param string $dysonCityId
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setDysonCityId($dysonCityId);

    /**
     * Get region_id
     * @return string|null
     */
    public function getRegionId();

    /**
     * Set region_id
     * @param string $regionId
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setRegionId($regionId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Dyson\SinglePageCheckout\Api\Data\DysonCityExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Dyson\SinglePageCheckout\Api\Data\DysonCityExtensionInterface $extensionAttributes
    );

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setStoreId($storeId);

    /**
     * Get city
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     * @param string $city
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface
     */
    public function setCity($city);
}
