<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperation;
use Pim\Bundle\EnrichBundle\MassEditAction\OperationJobLauncher;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
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

    /**
     * @param MassActionParametersParser          $parameterParser
     * @param GridFilterAdapterInterface          $filterAdapter
     * @param OperationJobLauncher                $operationJobLauncher
     * @param ConverterInterface                  $operationConverter
     * @param ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory
     */
    public function __construct(
        MassActionParametersParser $parameterParser,
        GridFilterAdapterInterface $filterAdapter,
        OperationJobLauncher $operationJobLauncher,
        ConverterInterface $operationConverter,
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory
    ) {
        $this->parameterParser      = $parameterParser;
        $this->filterAdapter        = $filterAdapter;
        $this->operationJobLauncher = $operationJobLauncher;
        $this->operationConverter   = $operationConverter;
        $this->productAndProductModelQueryBuilderFactory = $productAndProductModelQueryBuilderFactory;
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
        $filters['products_count'] = $this->getProductCounts($filters);

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
     */
    private function getProductCounts(array $filters): int
    {
        $pqbOptions = ['filters' => $filters];

        foreach ($filters[0]['value'] as $code) {
            if ($this->isProductModelIdentifier($code)) {
                $filters[] = [
                    'field' => 'parent',
                    'operator' => Operators::IN_LIST,
                    'value' => $code
                ];
            }
        }



        return $this->productAndProductModelQueryBuilderFactory->create($pqbOptions)->execute()->count();
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
}
