<?php

namespace Aligent\Stockists\Model\Resolver;

use Aligent\Stockists\Api\StockistRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Stockist implements ResolverInterface
{
    /**
     * @var StockistRepositoryInterface
     */
    private $stockistRepository;

    public function __construct(StockistRepositoryInterface $stockistRepository)
    {
        $this->stockistRepository = $stockistRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['identifier']) && !isset($args['url_key'])) {
            throw new GraphQlInputException(__('Identifier or url_key must be provided.'));
        }

        try {
            if (isset($args['identifier'])) {
                $result = $this->stockistRepository->get($args['identifier']);
            } elseif (isset($args['url_key'])) {
                $result = $this->stockistRepository->getByUrlKey($args['url_key']);
            } else {
                throw new GraphQlInputException(__('The provided identifier or url_key is invalid.'));
            }
            if (!$result->getIsActive()) {
                throw new GraphQlInputException(__('The provided identifier or url_key is invalid.'));
            }
            $locationData = $result->getData();
            $locationData['model'] = $result;
            return $locationData;
        } catch (NoSuchEntityException $ex) {
            throw new GraphQlInputException(__('The provided identifier is invalid.'));
        }
    }
}
