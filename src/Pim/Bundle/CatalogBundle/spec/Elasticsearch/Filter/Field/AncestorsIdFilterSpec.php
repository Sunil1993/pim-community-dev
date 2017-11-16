<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AncestorsIdFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class AncestorsIdFilterSpec extends ObjectBehavior
{
    public function let(
        ProductModelRepositoryInterface $productModelRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($productModelRepository, $productRepository, [Operators::IN_LIST]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AncestorsIdFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->supportsOperator(Operators::IN_LIST)->shouldReturn(true);
        $this->supportsOperator(Operators::EQUALS)->shouldReturn(false);
        $this->supportsOperator(Operators::NOT_IN_LIST)->shouldReturn(false);
    }

    function it_supports_ancestors_id_field()
    {
        $this->supportsField('ancestors.ids')->shouldReturn(true);
        $this->supportsField('ancestors.codes')->shouldReturn(false);
        $this->supportsField('wrong_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_in_list(
        SearchQueryBuilder $sqb,
        $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $sqb->addFilter(
            [
                'terms' => ['ancestors.ids' => ['product_model_1', 'product_model_2']],
            ]
        )->shouldBeCalled();

        $productModelRepository->findOneBy(['id' => 1])->willReturn($productModel1);
        $productModelRepository->findOneBy(['id' => 2])->willReturn($productModel2);

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'ancestors.ids',
            Operators::IN_LIST,
            ['product_model_1', 'product_model_2'],
            null,
            null,
            []
        );
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['ancestors.ids', Operators::EQUALS, 'product_model_id', null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                AncestorsIdFilter::class
            )
        )->during('addFieldFilter', ['ancestors.ids', Operators::IN_CHILDREN_LIST, null, null, null, []]);
    }

    function it_throws_an_exception_if_the_value_is_not_an_array(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected('ancestors', AncestorsIdFilter::class, 123)
        )->during('addFieldFilter', ['parent', Operators::IN_LIST, 123, null, null, []]);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected('ancestors', AncestorsIdFilter::class, 'wrong_value')
        )->during('addFieldFilter', ['parent', Operators::IN_LIST, 'wrong_value', null, null, []]);
    }

    function it_throws_an_exception_if_the_value_is_not_a_product_model_nor_a_product_id(
        $productModelRepository,
        $productRepository,
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $productModelRepository->findOneBy(['id' => 0])->willReturn(null);
        $productRepository->findOneBy(['id' => 0])->willReturn(null);

        $sqb->addFilter()->shouldNotBeCalled();

        $this->shouldThrow(
            new ObjectNotFoundException(
                'Object "product model" or "product" with code "invalid_identifier" does not exist'
            )
        )->during('addFieldFilter', ['ancestors.ids', Operators::IN_LIST, ['invalid_identifier'], null, null, []]);
    }
}
