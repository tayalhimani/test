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

namespace Dyson\AmastyCheckoutExtension\Model;

use Magento\Framework\Api\DataObjectHelper;
use Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterfaceFactory;
use Dyson\AmastyCheckoutExtension\Api\Data\DysonCityInterface;

class DysonCity extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'dyson_city';
    protected $dyson_cityDataFactory;

    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param DysonCityInterfaceFactory $dyson_cityDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Dyson\AmastyCheckoutExtension\Model\ResourceModel\DysonCity $resource
     * @param \Dyson\AmastyCheckoutExtension\Model\ResourceModel\DysonCity\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        DysonCityInterfaceFactory $dyson_cityDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Dyson\AmastyCheckoutExtension\Model\ResourceModel\DysonCity $resource,
        \Dyson\AmastyCheckoutExtension\Model\ResourceModel\DysonCity\Collection $resourceCollection,
        array $data = []
    ) {
        $this->dyson_cityDataFactory = $dyson_cityDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve dyson_city model with dyson_city data
     * @return DysonCityInterface
     */
    public function getDataModel()
    {
        $dyson_cityData = $this->getData();
        
        $dyson_cityDataObject = $this->dyson_cityDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dyson_cityDataObject,
            $dyson_cityData,
            DysonCityInterface::class
        );
        
        return $dyson_cityDataObject;
    }
}
