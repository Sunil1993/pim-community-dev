<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\ORM\Query;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\SelectedForMassEdit;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class SelectedForMassEditSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($productAndProductModelQueryBuilderFactory, $productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectedForMassEdit::class);
    }

    function it_returns_the_catalog_products_count_when_a_user_selects_all_products_in_the_grid(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [];

        $productAndProductModelQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('entity_type', Operators::EQUALS,ProductInterface::class)->shouldBeCalled();
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(2500);

        $this->findImpactedProducts($pqbFilters)->shouldReturn(2500);
    }

    public function it_returns_all_the_selected_products_count_when_a_user_selects_a_list_of_products(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_1', 'product_2', 'product_3'],
                'context' => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('ancestors.ids', Operators::IN_LIST, ['product_1', 'product_2', 'product_3'])->shouldBeCalled();
        $pqb->addFilter('entity_type', Operators::EQUALS,ProductInterface::class)->shouldBeCalled();
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(3);

        $this->findImpactedProducts($pqbFilters)->shouldReturn(3);
    }

    public function it_returns_all_the_selected_variant_products_when_a_user_selects_a_product_model(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_model_1'],
                'context' => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('ancestors.ids', Operators::IN_LIST, ['product_model_1'])->shouldBeCalled();
        $pqb->addFilter('entity_type', Operators::EQUALS,ProductInterface::class)->shouldBeCalled();
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(10);

        $this->findImpactedProducts($pqbFilters)->shouldReturn(10);
    }

    public function it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks(
        $productAndProductModelQueryBuilderFactory,
        $productRepository,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_1', 'product_2'],
                'context'  => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter('ancestors.ids', Operators::IN_LIST, ['product_1', 'product_2'])->shouldBeCalled();
        $pqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(2);

        $productRepository->countAll()->willReturn(2500);

        $this->findImpactedProducts($pqbFilters)->shouldReturn(2498);
    }

    public function it_computes_when_a_user_selects_all_visible()
    {
    }

    public function it_computes_when_a_user_selects_all_visible_along_with_other_filters()
    {
    }
}
