<?php

namespace Ladb\CoreBundle\Repository\Core;

use Ladb\CoreBundle\Entity\Core\User;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class ViewRepository extends AbstractEntityRepository
{

    /////

    public function existsByEntityTypeAndEntityIdAndUserAndKind($entityType, $entityId, User $user, $kind)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'count(v.id)' ))
            ->from($this->getEntityName(), 'v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId = :entityId')
            ->andWhere('v.user = :user')
            ->andWhere('v.kind = :kind')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
            ->setParameter('user', $user)
            ->setParameter('kind', $kind)
        ;

        try {
            return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return false;
        }
    }

    /////

    public function countByEntityTypeAndEntityIdsAndUserAndKind($entityType, $entityIds, User $user, $kind)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'count(v.id)' ))
            ->from($this->getEntityName(), 'v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId IN (' . implode(',', $entityIds) . ')')
            ->andWhere('v.user = :user')
            ->andWhere('v.kind = :kind')
            ->setParameter('entityType', $entityType)
            ->setParameter('user', $user)
            ->setParameter('kind', $kind)
        ;

        try {
            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return 0;
        }
    }

    /////

    public function findOneByEntityTypeAndEntityIdAndUserAndKind($entityType, $entityId, User $user, $kind)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'v' ))
            ->from($this->getEntityName(), 'v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId = :entityId')
            ->andWhere('v.user = :user')
            ->andWhere('v.kind = :kind')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
            ->setParameter('user', $user)
            ->setParameter('kind', $kind)
        ;

        try {
            $result = $queryBuilder->getQuery()->getResult();
            if (count($result) > 0) {
                return $result[0];
            }
            return null;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /////

    public function findByEntityTypeAndEntityId($entityType, $entityId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'v' ))
            ->from($this->getEntityName(), 'v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId = :entityId')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
        ;

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findByEntityTypeAndEntityIdAndKind($entityType, $entityId, $kind)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'v' ))
            ->from($this->getEntityName(), 'v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId = :entityId')
            ->andWhere('v.kind = :kind')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
            ->setParameter('kind', $kind)
        ;

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findByEntityTypeAndEntityIdsAndUserAndKind($entityType, $entityIds, User $user, $kind)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'v' ))
            ->from($this->getEntityName(), 'v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId IN (' . implode(',', $entityIds) . ')')
            ->andWhere('v.user = :user')
            ->andWhere('v.kind = :kind')
            ->setParameter('entityType', $entityType)
            ->setParameter('user', $user)
            ->setParameter('kind', $kind)
        ;

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /////

    public function findLastCreatedByEntityTypeAndUserAndKindGreaterOrEquals($entityType, User $user, $kind)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'v' ))
            ->from($this->getEntityName(), 'v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.user = :user')
            ->andWhere('v.kind >= :kind')
            ->orderBy('v.createdAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('entityType', $entityType)
            ->setParameter('user', $user)
            ->setParameter('kind', $kind)
        ;
        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }
}
