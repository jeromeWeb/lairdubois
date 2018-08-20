<?php

namespace Ladb\CoreBundle\Repository\Qa;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ladb\CoreBundle\Entity\Blog\Question;
use Ladb\CoreBundle\Entity\Core\User;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class QuestionRepository extends AbstractEntityRepository
{

    /////

    public function getDefaultJoinOptions()
    {
        return array( array( 'inner', 'user', 'u' ) );
    }

    /////

    public function findOneByIdJoinedOnUser($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->where('q.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findOneByIdJoinedOnOptimized($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u', 'bbs' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->innerJoin('q.bodyBlocks', 'bbs')
            ->where('q.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findOneFirstByUser(User $user)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->where('q.isDraft = false')
            ->andWhere('q.user = :user')
            ->orderBy('q.id', 'ASC')
            ->setParameter('user', $user)
            ->setMaxResults(1);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findOneLastByUser(User $user)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->where('q.isDraft = false')
            ->andWhere('q.user = :user')
            ->orderBy('q.id', 'DESC')
            ->setParameter('user', $user)
            ->setMaxResults(1);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findOnePreviousByUserAndId(User $user, $id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->where('q.isDraft = false')
            ->andWhere('q.user = :user')
            ->andWhere('q.id < :id')
            ->orderBy('q.id', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('id', $id)
            ->setMaxResults(1);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findOneNextByUserAndId(User $user, $id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->where('q.isDraft = false')
            ->andWhere('q.user = :user')
            ->andWhere('q.id > :id')
            ->orderBy('q.id', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('id', $id)
            ->setMaxResults(1);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /////

    public function findByIds(array $ids)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->where($queryBuilder->expr()->in('q.id', $ids))
        ;

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /////

    private function _applyCommonFilter(&$queryBuilder, $filter)
    {
        if ('popular-views' == $filter) {
            $queryBuilder
                ->addOrderBy('q.viewCount', 'DESC')
            ;
        } elseif ('popular-likes' == $filter) {
            $queryBuilder
                ->addOrderBy('q.likeCount', 'DESC')
            ;
        } elseif ('popular-comments' == $filter) {
            $queryBuilder
                ->addOrderBy('q.commentCount', 'DESC')
            ;
        }
        $queryBuilder
            ->addOrderBy('q.changedAt', 'DESC')
        ;
    }

    public function findPagined($offset, $limit, $filter = 'recent', $includeDrafts = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ('draft' == $filter && $includeDrafts) {
            $queryBuilder
                ->andWhere('q.isDraft = true')
            ;
        } elseif (!$includeDrafts) {
            $queryBuilder
                ->andWhere('q.isDraft = false')
            ;
        }

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByUser(User $user, $offset, $limit, $filter = 'recent', $includeDrafts = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'q', 'u' ))
            ->from($this->getEntityName(), 'q')
            ->innerJoin('q.user', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ('draft' == $filter && $includeDrafts) {
            $queryBuilder
                ->andWhere('q.isDraft = true')
            ;
        } elseif (!$includeDrafts) {
            $queryBuilder
                ->andWhere('q.isDraft = false')
            ;
        }

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }
}
