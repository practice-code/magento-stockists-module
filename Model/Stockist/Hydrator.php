<?php
/**
 * Copyright © Aligent Consulting. All rights reserved.
 */
declare(strict_types=1);

namespace Aligent\Stockists\Model\Stockist;

use Aligent\Stockists\Api\Data\StockistDataProcessorInterface;
use Aligent\Stockists\Api\Data\StockistInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\MapperPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Validation\ValidationException;

/**
 * Class StockistHydrator
 * @api
 */
class Hydrator implements \Magento\Framework\EntityManager\HydratorInterface
{

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var StockistDataProcessorInterface[]|array
     */
    protected $dataProcessors;

    /**
     * @var MapperPool
     */
    protected $mapperPool;

    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * StockistHydrator constructor.
     * @param DataObjectProcessor $dataObjectProcessor
     * @param MapperPool $mapperPool
     * @param TypeResolver $typeResolver
     * @param DataObjectHelper $dataObjectHelper
     * @param StockistDataProcessorInterface[] $dataProcessors
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        MapperPool $mapperPool,
        TypeResolver $typeResolver,
        DataObjectHelper $dataObjectHelper,
        array $dataProcessors = []
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->typeResolver = $typeResolver;
        $this->mapperPool = $mapperPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProcessors = $dataProcessors;
    }

    /**
     * @param object $entity
     * @return array
     * @throws \Exception
     */
    public function extract($entity)
    {
        $entityType = $this->typeResolver->resolve($entity);
        $data = $this->dataObjectProcessor->buildOutputDataArray($entity, $entityType);
        $mapper = $this->mapperPool->getMapper($entityType);
        return $mapper->entityToDatabase($entityType, $data);
    }

    /**
     * @param StockistInterface $stockist
     * @param array $data
     * @return StockistInterface
     * @throws ValidationException
     */
    public function hydrate($stockist, array $data): StockistInterface
    {
        if (empty($data[StockistInterface::STOCKIST_ID])) {
            unset($data[StockistInterface::STOCKIST_ID]);
        }
        if (isset($data['allow_store_delivery']) && $data['allow_store_delivery'] == 'true') {
            $data['allow_store_delivery'] = 1;
        } else {
            $data['allow_store_delivery'] = 0;
        }

        if (empty($data[StockistInterface::COUNTRY]) && !empty($data['country_id'])) {
            // possible todo: convert to full name?
            $data[StockistInterface::COUNTRY] = $data['country_id'];
        }

        foreach ($this->dataProcessors as $dataProcessor) {
            if ($dataProcessor instanceof StockistDataProcessorInterface) {
                $data = $dataProcessor->execute($data);
            }
        }
        try {
            $this->dataObjectHelper->populateWithArray($stockist, $data, StockistInterface::class);
        } catch (\Exception $e) {
            throw new ValidationException(__($e->getMessage()));
        }
        return $stockist;
    }
}
