<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\Query;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SelectedForMassEditInterface
{
    /**
     * Depending on some pqb filters given, can determine the total number of products that are contained in
     * this selection.
     *
     * @param array $filters
     *
     * @return int
     */
    public function findImpactedProducts(array $filters): int;
}
