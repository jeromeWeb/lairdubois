<?php

namespace Ladb\CoreBundle\Repository\Promotion;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ladb\CoreBundle\Entity\Blog\Post;
use Ladb\CoreBundle\Entity\Core\User;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class GraphicRepository extends AbstractEntityRepository
{

    /////

    public function findOneByIdJoinedOnUser($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'g', 'u', 'mp' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->innerJoin('g.mainPicture', 'mp')
            ->where('g.id = :id')
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
            ->select(array( 'g', 'u', 'uav', 'mp', 'tgs', 'l' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->innerJoin('u.avatar', 'uav')
            ->innerJoin('g.mainPicture', 'mp')
            ->innerJoin('g.tags', 'tgs')
            ->innerJoin('g.license', 'l')
            ->where('g.id = :id')
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
            ->select(array( 'g', 'u', 'mp' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->innerJoin('g.mainPicture', 'mp')
            ->where('g.isDraft = false')
            ->andWhere('g.user = :user')
            ->orderBy('g.id', 'ASC')
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
            ->select(array( 'g', 'u', 'mp' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->innerJoin('g.mainPicture', 'mp')
            ->where('g.isDraft = false')
            ->andWhere('g.user = :user')
            ->orderBy('g.id', 'DESC')
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
            ->select(array( 'g', 'u', 'mp' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->innerJoin('g.mainPicture', 'mp')
            ->where('g.isDraft = false')
            ->andWhere('g.user = :user')
            ->andWhere('g.id < :id')
            ->orderBy('g.id', 'DESC')
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
            ->select(array( 'g', 'u', 'mp' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->innerJoin('g.mainPicture', 'mp')
            ->where('g.isDraft = false')
            ->andWhere('g.user = :user')
            ->andWhere('g.id > :id')
            ->orderBy('g.id', 'ASC')
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
            ->select(array( 'g', 'u', 'mp' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->innerJoin('g.mainPicture', 'mp')
            ->where($queryBuilder->expr()->in('g.id', $ids))
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
                ->addOrderBy('g.viewCount', 'DESC')
            ;
        } elseif ('popular-likes' == $filter) {
            $queryBuilder
                ->addOrderBy('g.likeCount', 'DESC')
            ;
        } elseif ('popular-comments' == $filter) {
            $queryBuilder
                ->addOrderBy('g.commentCount', 'DESC')
            ;
        } elseif ('popular-downloads' == $filter) {
            $queryBuilder
                ->addOrderBy('g.downloadCount', 'DESC')
            ;
        }
        $queryBuilder
            ->addOrderBy('g.changedAt', 'DESC')
        ;
    }

    public function findPaginedByUser(User $user, $offset, $limit, $filter = 'recent', $includeDrafts = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'g', 'u' ))
            ->from($this->getEntityName(), 'g')
            ->innerJoin('g.user', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ('draft' == $filter && $includeDrafts) {
            $queryBuilder
                ->andWhere('g.isDraft = true')
            ;
        } elseif (!$includeDrafts) {
            $queryBuilder
                ->andWhere('g.isDraft = false')
            ;
        }

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }
}
