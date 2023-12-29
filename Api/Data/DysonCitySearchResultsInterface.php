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

interface DysonCitySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get dyson_city list.
     * @return \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface[]
     */
    public function getItems();

    /**
     * Set region_id list.
     * @param \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
