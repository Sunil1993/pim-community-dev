<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperation;
use Pim\Bundle\EnrichBundle\MassEditAction\OperationJobLauncher;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Pim\Component\Enrich\Query\SelectedForMassEditInterface;
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

    /** @var SelectedForMassEditInterface */
    private $selectedForMassEdit;

    /**
     * @param MassActionParametersParser          $parameterParser
     * @param GridFilterAdapterInterface          $filterAdapter
     * @param OperationJobLauncher                $operationJobLauncher
     * @param ConverterInterface                  $operationConverter
     * @param ProductModelRepositoryInterface     $productModelRepository
     * @param ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param SelectedForMassEditInterface        $selectedForMassEdit
     */
    public function __construct(
        MassActionParametersParser $parameterParser,
        GridFilterAdapterInterface $filterAdapter,
        OperationJobLauncher $operationJobLauncher,
        ConverterInterface $operationConverter,
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderFactoryInterface $productAndProductModelQueryBuilderFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        SelectedForMassEditInterface $selectedForMassEdit
    ) {
        $this->parameterParser      = $parameterParser;
        $this->filterAdapter        = $filterAdapter;
        $this->operationJobLauncher = $operationJobLauncher;
        $this->operationConverter   = $operationConverter;
        $this->productAndProductModelQueryBuilderFactory = $productAndProductModelQueryBuilderFactory;
        $this->productModelRepository = $productModelRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->selectedForMassEdit = $selectedForMassEdit;
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
        $filters['products_count'] = $this->selectedForMassEdit->findImpactedProducts($filters);

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
}
