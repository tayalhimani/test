<?php
/**
 * City table creation
 * Copyright (C) 2019
 *
 * This file is part of Dyson/AmastyCheckoutExtension.
 *
 * Dyson/AmastyCheckoutExtension is free software: you can redistribute it and/or modify
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

namespace Dyson\AmastyCheckoutExtension\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface DysonCityRepositoryInterface
{

    /**
     * Save dyson_city
     * @param \Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterface $dysonCity
     * @return \Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterface $dysonCity
    );

    /**
     * Retrieve dyson_city
     * @param string $dysonCityId
     * @return \Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($dysonCityId);

    /**
     * Retrieve dyson_city matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Dyson\AmastyCheckoutExtension\Api\Data\DysonCitySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete dyson_city
     * @param \Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterface $dysonCity
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterface $dysonCity
    );

    /**
     * Delete dyson_city by ID
     * @param string $dysonCityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($dysonCityId);
}
