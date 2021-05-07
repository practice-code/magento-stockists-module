<?php

namespace Aligent\Stockists\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class StockistAddress implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var \Aligent\Stockists\Api\Data\StockistInterface $stockist */
        $stockist = $value['model'];

        return [
            'street' => $stockist->getStreet(),
            'city' => $stockist->getCity(),
            'suburb' => $stockist->getSuburb(),
            'postcode' => $stockist->getPostcode(),
            'region' => $stockist->getRegion(),
            'country_code' => $stockist->getCountry(),
            'phone' => $stockist->getPhone()
        ];
    }
}
