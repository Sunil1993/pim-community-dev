<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\ORM\Query;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\SelectedForMassEdit;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

class SelectedForMassEditSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        $this->beConstructedWith($productAndProductModelQueryBuilderFactory, $productQueryBuilderFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectedForMassEdit::class);
    }

    function it_returns_the_catalog_products_count_when_a_user_selects_all_products_in_the_grid(
        $productAndProductModelQueryBuilderFactory,
        $productQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [];

        $productQueryBuilderFactory->create(['filters' => []])->willReturn($pqb);
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(2500);

        $productAndProductModelQueryBuilderFactory->create()->shouldNotBeCalled();

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

    public function it_returns_all_the_selected_variant_products_when_a_user_selects_product_models_and_products(
        $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'value' => ['product_model_1', 'product_model_2', 'product_1', 'product_2'],
                'context' => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->addFilter(
            'ancestors.ids',
            Operators::IN_LIST,
            ['product_model_1', 'product_model_2', 'product_1', 'product_2']
        )->shouldBeCalled();
        $pqb->addFilter('entity_type', Operators::EQUALS,ProductInterface::class)->shouldBeCalled();
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(10);

        $this->findImpactedProducts($pqbFilters)->shouldReturn(10);
    }

    public function it_substracts_the_product_catalog_count_to_the_selected_entities_when_a_user_selects_all_and_unchecks(
        $productAndProductModelQueryBuilderFactory,
        $productQueryBuilderFactory,
        ProductQueryBuilderInterface $ppmqb,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable1,
        \Countable $countable2
    ) {
        $pqbFilters = [
            [
                'field'    => 'id',
                'operator' => 'NOT IN',
                'value'    => ['product_1', 'product_2'],
                'context'  => []
            ]
        ];

        $productAndProductModelQueryBuilderFactory->create()->willReturn($ppmqb);
        $ppmqb->addFilter('ancestors.ids', Operators::IN_LIST, ['product_1', 'product_2'])->shouldBeCalled();
        $ppmqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $ppmqb->execute()->willReturn($countable1);
        $countable1->count()->willReturn(2);

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $pqb->execute()->willReturn($countable2);
        $countable2->count()->willReturn(2500);

        $this->findImpactedProducts($pqbFilters)->shouldReturn(2498);
    }

    public function it_computes_when_a_user_selects_all_entities_with_other_filters(
        $productAndProductModelQueryBuilderFactory,
        $productQueryBuilderFactory,
        ProductQueryBuilderInterface $pqb,
        \Countable $countable
    ) {
        $pqbFilters = [
            [
                'field' => 'color',
                'operator' => '=',
                'value' => 'red',
                'context' => []
            ],
            [
                'field' => 'size',
                'operator' => 'IN LIST',
                'value' => ['l', 'm'],
                'context' => []
            ]
        ];

        $productQueryBuilderFactory->create(
            [
                'filters' => [
                    [
                        'field' => 'color',
                        'operator' => '=',
                        'value' => 'red',
                        'context' => []
                    ],
                    [
                        'field' => 'size',
                        'operator' => 'IN LIST',
                        'value' => ['l', 'm'],
                        'context' => []
                    ]
                ]
            ]
        )->willReturn($pqb);
        $pqb->execute()->willReturn($countable);
        $countable->count()->willReturn(12);

        $productAndProductModelQueryBuilderFactory->create()->shouldNotBeCalled();

        $this->findImpactedProducts($pqbFilters)->shouldReturn(12);
    }
}
