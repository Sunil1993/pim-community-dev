<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * Search use cases of products selected through their product model in the datagrid prior to start a mass edit.
 *
 * - Find all impacted products given a selection of product model ids and codes.
 * - Find all entities to be updated given some attributes to update and a selection of product models ids and codes.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchImpactedProductsInMassActionIntegration extends AbstractPimCatalogProductModelIntegration
{
    private const PRODUCT_DOCUMENT_TYPE = 'Pim\\\\Component\\\\Catalog\\\\Model\\\\ProductInterface';

    public function testFindAllImpactedProductsForRootProductModelId()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            // Self + subtree of node 'product_model_1'
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => ['product_model_1'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_1'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query'         => self::PRODUCT_DOCUMENT_TYPE,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'tshirt-grey-s',
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
            ]
        );
    }

    public function testFindAllImpactedProductsForRootProductModelCode()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'identifier' => ['model-tshirt'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['model-tshirt'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query'         => self::PRODUCT_DOCUMENT_TYPE,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'tshirt-grey-s',
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
            ]
        );
    }

    public function testFindAllImpactedProductsForSubProductModelId()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => ['product_model_11'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_11'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query'         => self::PRODUCT_DOCUMENT_TYPE,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'running-shoes-l-blue',
                'running-shoes-l-white',
                'running-shoes-l-red',
            ]
        );
    }

    public function testFindAllImpactedProductsForSubProductModelCode()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'identifier' => ['model-biker-jacket-polyester'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['model-biker-jacket-polyester'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query'         => self::PRODUCT_DOCUMENT_TYPE,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'biker-jacket-polyester-s',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-l',
            ]
        );
    }

    public function testFindAllImpactedProductsForProductsAndProductModelIds()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => [
                                            'product_model_1',
                                            'product_model_10',
                                            'product_model_6',
                                            'product_17',
                                        ],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => [
                                            'product_model_1',
                                            'product_model_10',
                                            'product_model_6',
                                            'product_17',
                                        ],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query'         => self::PRODUCT_DOCUMENT_TYPE,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'tshirt-grey-s',
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
                'hat-m',
                'hat-l',
                'watch',
            ]
        );
    }

    public function testFindProductModelAndAttributeInLastLevel()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'identifier' => ['model-tshirt'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['model-tshirt'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'terms' => [
                                        'attributes_for_this_level' => ['weight'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'tshirt-grey-s',
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
            ]
        );
    }

    public function testFindProductsToUpdateGivenASubProductModelAndAttributeInLastLevel()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'identifier' => ['model-tshirt-blue'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['model-tshirt-blue'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'terms' => [
                                        'attributes_for_this_level' => ['weight'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
            ]
        );
    }

    public function testFindProductToUpdateGivenASimpleProductAndAttributeInLastLevel()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'identifier' => ['tshirt-blue-s'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['tshirt-blue-s'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'terms' => [
                                        'attributes_for_this_level' => ['weight'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'tshirt-blue-s',
            ]
        );
    }

    public function testFindEntitiesToUpdateGivenEntitiesFromDifferentFamilyVariantsAndOneUpdate()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => ['product_model_1', 'product_model_6'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_1', 'product_model_6'],
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'terms' => [
                                        'attributes_for_this_level' => ['weight'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'tshirt-grey-s',
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
                'hat-m',
                'hat-l',
            ]
        );
    }
}
