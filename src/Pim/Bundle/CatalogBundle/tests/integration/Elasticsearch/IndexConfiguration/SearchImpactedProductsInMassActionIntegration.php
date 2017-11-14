<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * Search use cases of products selected through their product model in the datagrid prior to start a mass edit.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchImpactedProductsInMassActionIntegration extends AbstractPimCatalogProductModelIntegration
{
    private const PRODUCT_DOCUMENT_TYPE = 'Pim\\\\Component\\\\Catalog\\\\Model\\\\ProductInterface';

    public function testFindAllProductsForRootProductModelId()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_1'],
                                    ],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query' => self::PRODUCT_DOCUMENT_TYPE
                                    ]
                                ]
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

    public function testFindAllProductsForRootProductModelCode()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['model-tshirt'],
                                    ],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query' => self::PRODUCT_DOCUMENT_TYPE
                                    ]
                                ]
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

    public function testFindAllProductsForSubProductModelId()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_11'],
                                    ],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query' => self::PRODUCT_DOCUMENT_TYPE
                                    ]
                                ]
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

    public function testFindAllProductsForSubProductModelCode()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['model-biker-jacket-polyester'],
                                    ],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query' => self::PRODUCT_DOCUMENT_TYPE
                                    ]
                                ]
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
}
