<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SubtreeFilter extends AbstractFieldFilter
{
    private const ANCESTOR_ID_ES_FIELD = 'ancestors.ids';
    private const SUBTREE_FIELD = 'subtree.id';

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param ProductRepositoryInterface      $productRepository
     * @param array                           $supportedOperators
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        ProductRepositoryInterface $productRepository,
        array $supportedOperators
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productRepository = $productRepository;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator): bool
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field): bool
    {
        return $field === self::SUBTREE_FIELD;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $values, $locale = null, $channel = null, $options = []): void
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!$this->supportsOperator($operator)) {
            throw InvalidOperatorException::notSupported($operator, SubtreeFilter::class);
        }

        $this->checkValues($values);

        $this->searchQueryBuilder->addShould(
            [
                [
                    'terms' => [
                        self::ANCESTOR_ID_ES_FIELD => $values,
                    ],
                ],
                [
                    'terms' => [
                        'id' => $values,
                    ],
                ],
            ]
        );
    }

    /**
     * Checks the value we want to filter on is valid
     *
     * @param $values
     *
     * @throws ObjectNotFoundException
     */
    private function checkValues($values): void
    {
        FieldFilterHelper::checkArray(self::ANCESTOR_ID_ES_FIELD, $values, static::class);
        foreach ($values as $value) {
            FieldFilterHelper::checkString(self::ANCESTOR_ID_ES_FIELD, $value, static::class);
            if (!$this->isValidId($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object "product model" or "product" with code "%s" does not exist', $value)
                );
            }
        }
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isValidId(string $value): bool
    {
        $id = IdEncoder::decode($value)['id'];
        if (null === $this->productModelRepository->findOneBy(['id' => $id]) &&
            null === $this->productRepository->findOneBy(['id' => $id])) {
            return false;
        }

        return true;
    }
}
