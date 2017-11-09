<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;

/**
 * Some PQBs are able to return objects of different types (eg, Product and Product models). In some cases it is useful
 * to filter only on one or the other entity type.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTypeFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws InvalidPropertyTypeException
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!in_array($field, $this->supportedFields)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported field name for entity filter, only "%s" are supported, "%s" given',
                    implode(',', $this->supportedFields),
                    $field
                )
            );
        }

        if (!in_array($operator, $this->supportedOperators)) {
            throw InvalidOperatorException::notSupported($operator, static::class);
        }

        $this->checkValue($field, $value);

        $value = str_replace('\\', '\\\\', $value);


        $this->searchQueryBuilder->addFilter(
            [
                'query_string' => [
                    'default_field' => 'document_type',
                    'query'         => $value,
                ],
            ]
        );
    }

    /**
     * Checks the given value is a string.
     *
     * @param $value
     *
     * @throws InvalidPropertyTypeException
     */
    private function checkValue($field, $value)
    {
        FieldFilterHelper::checkString($field, $value, static::class);
    }
}
