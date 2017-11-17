<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Query;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Enrich\Query\SelectedForMassEditInterface;

/**
 * Given a list of PQB filters, determine the number of products within that selection.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectedForMassEdit implements SelectedForMassEditInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $productAndProductModelQueryBuilderFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /**
     * @param ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        $this->productAndProductModelQueryBuilderFactory = $productAndProductModelQueryBuilderFactory;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findImpactedProducts(array $filters): int
    {
        $impactedProducts = $this->countImpactedProducts($filters);

        if ($this->isSelectionUnchecked($filters)) {
            return $this->getTotalProductsCount() - $impactedProducts;
        }

        return $impactedProducts;
    }

    /**
     * @param array $filters
     *
     * @return int
     */
    private function countImpactedProducts(array $filters): int
    {
        $ids = $this->extractIdsFromPqbFilters($filters);
        if (empty($ids)) {
            $attributeAndFieldFilters = $this->extractAttributeAndFieldFilters($filters);
            $impactedProducts = $this->searchImpactedProducts($attributeAndFieldFilters);
        } else {
            $impactedProducts = $this->searchImpactedProductsInProductModelTrees($ids);
        }

        return $impactedProducts;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function extractIdsFromPqbFilters(array $filters): array
    {
        $productAndProductModelIds = [];
        foreach ($filters as $condition) {
            if ('id' === $condition['field'] &&
                in_array($condition['operator'], [Operators::IN_LIST, Operators::NOT_IN_LIST])
            ) {
                $productAndProductModelIds = array_merge($productAndProductModelIds, $condition['value']);
            }
        }

        return $productAndProductModelIds;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function extractAttributeAndFieldFilters(array $filters): array
    {
        $attributeAndFieldFilters = [];
        foreach ($filters as $condition) {
            if ('id' !== $condition['field']) {
                $attributeAndFieldFilters[] = $condition;
            }
        }

        return ['filters' => $attributeAndFieldFilters];
    }

    /**
     * @return int
     */
    private function getTotalProductsCount(): int
    {
        return $this->productQueryBuilderFactory->create()->execute()->count();
    }

    /**
     * @param $filters
     *
     * @return bool
     */
    private function isSelectionUnchecked($filters): bool
    {
        foreach ($filters as $condition) {
            if ('id' === $condition['field'] && Operators::NOT_IN_LIST === $condition['operator']) {
                return true;
            }
        }

        return false;
    }

    /**
     * for the case where a user has selected ALL products in the datagrid and also has filtered some of them with
     * attributes or field filters, we use the product query builder to find only products.
     *
     * There is no way at the moment, to not activate a grouped view with the ProductAndProductQueryBuilder when passing
     * onto it only a couple of filters.
     *
     * @param array $filters
     *
     * @return int
     */
    private function searchImpactedProducts($filters): int
    {
        return $this->productQueryBuilderFactory->create($filters)->execute()->count();
    }

    /**
     * @param array $attributeAndFieldFilters
     * @param array $ids
     *
     * @return int
     */
    private function searchImpactedProductsInProductModelTrees(array $ids): int
    {
        $pqb = $this->productAndProductModelQueryBuilderFactory->create();
        $pqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);
        if (!empty($ids)) {
            $pqb->addFilter('ancestors.ids', Operators::IN_LIST, $ids);
        }
        $impactedProducts = $pqb->execute()->count();

        return $impactedProducts;
    }
}
