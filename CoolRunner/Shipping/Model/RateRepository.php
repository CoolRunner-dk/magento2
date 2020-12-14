<?php
/**
 *  RateRepository
 *
 * @copyright Copyright © 2020 HeadWayIt https://headwayit.com/ All rights reserved.
 * @author  Ilya Kushnir  ilya.kush@gmail.com
 * Date:    25.08.2020
 * Time:    11:41
 */
namespace CoolRunner\Shipping\Model;

use CoolRunner\Shipping\Model\Rate as Model;
use CoolRunner\Shipping\Model\ResourceModel\Rate as ResourceModel;
use CoolRunner\Shipping\Model\ResourceModel\Rate\Collection as Collection;
use CoolRunner\Shipping\Model\ResourceModel\Rate\CollectionFactory as CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
/**
 * Class RateRepository
 *
 * @package CoolRunner\Shipping
 */
class RateRepository {
    /**
     * @var RateFactory
     */
    protected $_modelFactory;
    /**
     * @var ResourceModel
     */
    protected $_resource;
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * RateRepository constructor.
     *
     * @param RateFactory       $modelFactory
     * @param ResourceModel     $resource
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(RateFactory $modelFactory, ResourceModel $resource, CollectionFactory $collectionFactory) {

        $this->_modelFactory = $modelFactory;
        $this->_resource = $resource;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @param Rate $model
     *
     * @return Rate
     * @throws CouldNotSaveException
     */
    public function save(Model $model){
        try {
            $this->_resource->save($model);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $model;
    }

    /**
     * @param Rate $model
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Model $model) {
        try {
            $this->_resource->delete($model);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($id) {
        $model = $this->_modelFactory->create();
        $this->_resource->load($model, $id);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The rate with the "%1" ID doesn\'t exist.', $id));
        }
        return $model;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id) {
        return $this->delete($this->getById($id));
    }

    /**
     * @return Model
     */
    public function getModelObject(){
        return $this->_modelFactory->create();
    }

    /**
     * @return Collection
     */
    public function getCollectionObject(){
        return $this->_collectionFactory->create();
    }
}
