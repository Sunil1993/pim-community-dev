<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperation;
use Pim\Bundle\EnrichBundle\MassEditAction\OperationJobLauncher;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mass edit controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditController
{
    /** @var MassActionParametersParser */
    protected $parameterParser;

    /** @var GridFilterAdapterInterface */
    protected $filterAdapter;

    /** @var OperationJobLauncher */
    protected $operationJobLauncher;

    /** @var ConverterInterface */
    protected $operationConverter;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productAndProductModelQueryBuilderFactory;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /**
     * @param MassActionParametersParser          $parameterParser
     * @param GridFilterAdapterInterface          $filterAdapter
     * @param OperationJobLauncher                $operationJobLauncher
     * @param ConverterInterface                  $operationConverter
     * @param ProductModelRepositoryInterface     $productModelRepository
     * @param ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     */
    public function __construct(
        MassActionParametersParser $parameterParser,
        GridFilterAdapterInterface $filterAdapter,
        OperationJobLauncher $operationJobLauncher,
        ConverterInterface $operationConverter,
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        $this->parameterParser      = $parameterParser;
        $this->filterAdapter        = $filterAdapter;
        $this->operationJobLauncher = $operationJobLauncher;
        $this->operationConverter   = $operationConverter;
        $this->productAndProductModelQueryBuilderFactory = $productAndProductModelQueryBuilderFactory;
        $this->productModelRepository = $productModelRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
    }

    /**
     * Get filters from datagrid request
     *
     * @return JsonResponse
     */
    public function getFilterAction(Request $request)
    {
        $parameters = $this->parameterParser->parse($request);
        $filters = $this->filterAdapter->adapt($parameters);
        $filters['products_count'] = $this->getProductsAndVariantProductsCount($filters);

        return new JsonResponse($filters);
    }

    /**
     * Launch mass edit action
     *
     * @return JsonResponse
     */
    public function launchAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $data = $this->operationConverter->convert($data);
        $operation = new MassEditOperation($data['jobInstanceCode'], $data['filters'], $data['actions']);
        $this->operationJobLauncher->launch($operation);

        return new JsonResponse();
    }

    /**
     * @param $filters
     *
     * @return int
     *
     * @throws \LogicException
     */
    private function getProductsAndVariantProductsCount(array $filters): int
    {
        $productsCount = $this->getProductsCount($filters);

        if ($this->isAllEntityQuery($filters)) {
            return $productsCount;
        }

        $variantProductsCount = $this->getVariantProductsCount($filters);
        $operator = $this->getOperator($filters);
        if (Operators::IN_LIST === $operator) {
            return $productsCount + $variantProductsCount;
        } elseif (Operators::NOT_IN_LIST === $operator) {
            return $productsCount - $variantProductsCount;
        } else {
            throw new \LogicException(
                sprintf('Unsupported operator found in the filter of mass action. "%s" given.', $operator)
            );
        }
    }

    /**
     * Checks wether the user has filtered on 'All entities' in the product grid.
     *
     * @param array $filters
     *
     * @return bool
     */
    private function isAllEntityQuery(array $filters): bool
    {
        return null === $this->getOperator($filters);
    }

    /**
     * @param array $filters
     *
     * @return null|string
     */
    private function getOperator(array $filters): ?string
    {
        return isset($filters[0]) && isset($filters[0]['operator']) ? $filters[0]['operator'] : null;
    }

    /**
     * @param array $filters
     *
     * @return int
     */
    private function getProductsCount(array $filters): int
    {
        $pqbOptionsProducts = $this->getPqbOptionsProducts($filters);
        if (empty($pqbOptionsProducts['filters']) && !empty($filters)) {
            return 0;
        }

        $productsCount = $this->productQueryBuilderFactory->create($pqbOptionsProducts)
            ->execute()
            ->count();

        return $productsCount;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function getPqbOptionsProducts(array $filters): array
    {
        $filteredFilters = [];

        foreach ($filters as $condition) {
            $productIds = [];
            foreach ($condition['value'] as $id) {
                if (!$this->isProductModelIdentifier($id)) {
                    $productIds[] = (string) IdEncoder::decode($id)['id'];
                }
            }
            if (!empty($productIds)) {
                $condition['value'] = $productIds;
                $filteredFilters[] = $condition;
            }
        }

        return ['filters' => $filteredFilters];
    }

    /**
     * Checks if the given code is a product model code.
     *
     * @param $code
     *
     * @return bool
     */
    private function isProductModelIdentifier(string $code): bool
    {
        return 0 === strpos($code, 'product_model');
    }

    /**
     * @param array $filters
     *
     * @return int
     */
    private function getVariantProductsCount(array $filters): int
    {
        $pqbOptionsVariantProducts = $this->generatePqbOptionsForVariantProducts($filters);
        if (empty($pqbOptionsVariantProducts['filters'])) {
            return 0;
        }

        return $this->productAndProductModelQueryBuilderFactory->create($pqbOptionsVariantProducts)
            ->execute()
            ->count();
    }

    /**
     * @param array $productsFilters
     *
     * @return array
     */
    private function generatePqbOptionsForVariantProducts(array $productsFilters): array
    {
        $productModelFilters = [];
        $productModelCodes = $this->getProductModelCodes($productsFilters);

        if (!empty($productModelCodes)) {
            $productModelFilters[] = [
                'field'    => 'parent',
                'operator' => Operators::IN_LIST,
                'value'    => $productModelCodes,
            ];
        }

        return ['filters' => $productModelFilters];
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function getProductModelCodes(array $filters): array
    {
        $allProductModelCodes = [];
        foreach ($filters[0]['value'] as $productModelId) {
            if ($this->isProductModelIdentifier($productModelId)) {
                $productModelId = IdEncoder::decode($productModelId)['id'];
                $productModel = $this->productModelRepository->findOneBy(['id' => $productModelId]);
                $productModelCodes = [];
                if (null !== $productModel) {
                    $productModelCodes[] = $productModel->getCode();
                    $subProductModelCodes = $this->getSubProductModelCodes($productModel);
                    if (!empty($subProductModelCodes)) {
                        $productModelCodes = $subProductModelCodes;
                    }
                }
                $allProductModelCodes = array_merge($allProductModelCodes, $productModelCodes);
            }
        }

        return $allProductModelCodes;
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return ProductModelInterface[]
     */
    private function getSubProductModelCodes(ProductModelInterface $productModel): array
    {
        $productModelCodes = [];
        foreach ($productModel->getProductModels() as $subProductModels) {
            $productModelCodes[] = $subProductModels->getCode();
        }

        return $productModelCodes;
    }
}
