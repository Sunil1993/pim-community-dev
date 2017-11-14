<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * Search use cases of products and product models using the "ancestors" field.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchAncestorsIntegration extends AbstractPimCatalogProductModelIntegration
{
    public function testFindAllProductsAndProductModelsAncestorsOfGrandParentWithCode()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'terms' => [
                                    'ancestors.codes' => ['model-tshirt'],
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
                'model-tshirt-grey',
                'tshirt-grey-s',
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
                'model-tshirt-blue',
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
                'model-tshirt-red',
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
            ]
        );
    }

    public function testFindAllProductsAndProductModelsAncestorsOfGrandParentWithId() {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'terms' => [
                                    'ancestors.ids' => ['product_model_8'],
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
                'model-running-shoes-s',
                'running-shoes-s-white',
                'running-shoes-s-blue',
                'running-shoes-s-red',
                'model-running-shoes-m',
                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
                'model-running-shoes-l',
                'running-shoes-l-white',
                'running-shoes-l-blue',
                'running-shoes-l-red',
            ]
        );
    }

    public function testFindAllProductsAndProductModelsAncestorsOfParentWithCodeForOneLevelFamilyVariant() {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'terms' => [
                                    'ancestors.codes' => ['model-tshirt-unique-size'],
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
                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',
            ]
        );
    }

    public function testFindAllProductsAndProductModelsAncestorsOfParentWithIdForOneLevelFamilyVariant() {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'terms' => [
                                    'ancestors.ids' => ['product_model_6'],
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
                'hat-m',
                'hat-l',
            ]
        );
    }

    public function testFindAllProductsAndProductModelsAncestorsOfParentWithCodeForTwoLevelFamilyVariant() {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'terms' => [
                                    'ancestors.codes' => ['model-tshirt-grey'],
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
            ]
        );
    }

    public function testFindAllProductsAndProductModelsAncestorsOfParentWithIdForTwoLevelFamilyVariant()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'terms' => [
                                    'ancestors.ids' => ['product_model_10'],
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
                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
            ]
        );
    }
}
