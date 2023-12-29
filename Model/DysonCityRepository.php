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

namespace Dyson\SinglePageCheckout\Model;

use Dyson\SinglePageCheckout\Model\ResourceModel\DysonCity as ResourceDysonCity;
use Dyson\SinglePageCheckout\Api\Data\DysonCitySearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Dyson\SinglePageCheckout\Api\Data\DysonCityInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Dyson\SinglePageCheckout\Model\ResourceModel\DysonCity\CollectionFactory as DysonCityCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Dyson\SinglePageCheckout\Api\DysonCityRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class DysonCityRepository implements DysonCityRepositoryInterface
{

    protected $dysonCityFactory;

    protected $dysonCityCollectionFactory;

    protected $dataDysonCityFactory;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    private $collectionProcessor;

    private $storeManager;

    protected $searchResultsFactory;

    protected $resource;

    protected $extensionAttributesJoinProcessor;

    protected $dataObjectProcessor;


    /**
     * @param ResourceDysonCity $resource
     * @param DysonCityFactory $dysonCityFactory
     * @param DysonCityInterfaceFactory $dataDysonCityFactory
     * @param DysonCityCollectionFactory $dysonCityCollectionFactory
     * @param DysonCitySearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceDysonCity $resource,
        DysonCityFactory $dysonCityFactory,
        DysonCityInterfaceFactory $dataDysonCityFactory,
        DysonCityCollectionFactory $dysonCityCollectionFactory,
        DysonCitySearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->dysonCityFactory = $dysonCityFactory;
        $this->dysonCityCollectionFactory = $dysonCityCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataDysonCityFactory = $dataDysonCityFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface $dysonCity
    ) {
        /* if (empty($dysonCity->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $dysonCity->setStoreId($storeId);
        } */

        $dysonCityData = $this->extensibleDataObjectConverter->toNestedArray(
            $dysonCity,
            [],
            \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface::class
        );

        $dysonCityModel = $this->dysonCityFactory->create()->setData($dysonCityData);

        try {
            $this->resource->save($dysonCityModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the dysonCity: %1',
                $exception->getMessage()
            ));
        }
        return $dysonCityModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($dysonCityId)
    {
        $dysonCity = $this->dysonCityFactory->create();
        $this->resource->load($dysonCity, $dysonCityId);
        if (!$dysonCity->getId()) {
            throw new NoSuchEntityException(__('dyson_city with id "%1" does not exist.', $dysonCityId));
        }
        return $dysonCity->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->dysonCityCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Dyson\SinglePageCheckout\Api\Data\DysonCityInterface $dysonCity
    ) {
        try {
            $dysonCityModel = $this->dysonCityFactory->create();
            $this->resource->load($dysonCityModel, $dysonCity->getDysonCityId());
            $this->resource->delete($dysonCityModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the dyson_city: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($dysonCityId)
    {
        return $this->delete($this->getById($dysonCityId));
    }
}
