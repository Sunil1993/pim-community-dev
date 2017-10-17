<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;

/**
 * Association repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeRepository extends EntityRepository implements AssociationTypeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findMissingAssociationTypes(ProductInterface $product)
    {
        $qb = $this->createQueryBuilder('a');

        if ($associations = $product->getAssociations()) {
            if ($associations instanceof Collection) {
                $associations = $associations->toArray();
            }

            $associationTypeIds = array_map(function ($association) {
                return $association->getAssociationType()->getId();
            }, $associations);

            if (!empty($associationTypeIds)) {
                $qb->andWhere(
                    $qb->expr()->notIn('a.id', $associationTypeIds)
                );
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        $qb = $this->createQueryBuilder('a');

        return (int) $qb
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }
}
