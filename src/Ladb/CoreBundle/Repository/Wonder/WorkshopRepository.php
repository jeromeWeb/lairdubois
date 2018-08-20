<?php

namespace Ladb\CoreBundle\Repository\Wonder;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ladb\CoreBundle\Entity\Howto\Howto;
use Ladb\CoreBundle\Entity\Core\User;
use Ladb\CoreBundle\Entity\Wonder\Plan;
use Ladb\CoreBundle\Entity\Workflow\Workflow;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class WorkshopRepository extends AbstractEntityRepository
{

    /////

    public function getDefaultJoinOptions()
    {
        return array( array( 'inner', 'user', 'u' ), array( 'inner', 'mainPicture', 'mp' ) );
    }

    /////

    public function findOneByIdJoinedOnUser($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->where('w.id = :id')
            ->setParameter('id', $id);
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
            ->select(array( 'w', 'u', 'uav', 'mp', 'ps', 'bbs', 'pls', 'hws', 'wfs', 'tgs', 'l' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('u.avatar', 'uav')
            ->innerJoin('w.mainPicture', 'mp')
            ->leftJoin('w.pictures', 'ps')
            ->leftJoin('w.bodyBlocks', 'bbs')
            ->leftJoin('w.plans', 'pls')
            ->leftJoin('w.howtos', 'hws')
            ->leftJoin('w.workflows', 'wfs')
            ->leftJoin('w.tags', 'tgs')
            ->leftJoin('w.license', 'l')
            ->where('w.id = :id')
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
            ->select(array( 'w', 'u', 'mp' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->where('w.isDraft = false')
            ->andWhere('w.user = :user')
            ->orderBy('w.id', 'ASC')
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
            ->select(array( 'w', 'u', 'mp' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->where('w.isDraft = false')
            ->andWhere('w.user = :user')
            ->orderBy('w.id', 'DESC')
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
            ->select(array( 'w', 'u', 'mp' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->where('w.isDraft = false')
            ->andWhere('w.user = :user')
            ->andWhere('w.id < :id')
            ->orderBy('w.id', 'DESC')
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
            ->select(array( 'w', 'u', 'mp' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->where('w.isDraft = false')
            ->andWhere('w.user = :user')
            ->andWhere('w.id > :id')
            ->orderBy('w.id', 'ASC')
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
            ->select(array( 'w', 'u', 'mp' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->where($queryBuilder->expr()->in('w.id', $ids))
        ;

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /////

    public function findPagined($offset, $limit, $filter = 'recent', $filterParam = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp' ))
            ->from($this->getEntityName(), 'w')
            ->join('w.user', 'u')
            ->join('w.mainPicture', 'mp')
            ->where('w.isDraft = false')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ('followed' == $filter) {
            $queryBuilder
                ->innerJoin('u.followers', 'f', 'WITH', 'f.user = :filterParam')
                ->setParameter('filterParam', $filterParam);
        }

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    private function _applyCommonFilter(&$queryBuilder, $filter)
    {
        if ('popular-views' == $filter) {
            $queryBuilder
                ->addOrderBy('w.viewCount', 'DESC')
            ;
        } elseif ('popular-likes' == $filter) {
            $queryBuilder
                ->addOrderBy('w.likeCount', 'DESC')
            ;
        } elseif ('popular-comments' == $filter) {
            $queryBuilder
                ->addOrderBy('w.commentCount', 'DESC')
            ;
        } elseif ('content-plans' == $filter) {
            $queryBuilder
                ->addOrderBy('w.planCount', 'DESC')
            ;
        } elseif ('content-howtos' == $filter) {
            $queryBuilder
                ->addOrderBy('w.howtoCount', 'DESC')
            ;
        } elseif ('content-videos' == $filter) {
            $queryBuilder
                ->addOrderBy('w.bodyBlockVideoCount', 'DESC')
            ;
        } elseif ('license-by' == $filter) {
            $queryBuilder
                ->innerJoin('w.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 1')
            ;
        } elseif ('license-by-nc' == $filter) {
            $queryBuilder
                ->innerJoin('w.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 0')
            ;
        } elseif ('license-by-nc-nd' == $filter) {
            $queryBuilder
                ->innerJoin('w.license', 'l')
                ->andWhere('l.allowDerivs = 0')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 0')
            ;
        } elseif ('license-by-nc-sa' == $filter) {
            $queryBuilder
                ->innerJoin('w.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 1')
                ->andWhere('l.allowCommercial = 0')
            ;
        } elseif ('license-by-nd' == $filter) {
            $queryBuilder
                ->innerJoin('w.license', 'l')
                ->andWhere('l.allowDerivs = 0')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 1')
            ;
        } elseif ('license-by-sa' == $filter) {
            $queryBuilder
                ->innerJoin('w.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 1')
                ->andWhere('l.allowCommercial = 1')
            ;
        }
        $queryBuilder
            ->addOrderBy('w.changedAt', 'DESC')
        ;
    }

    public function findPaginedByUser(User $user, $offset, $limit, $filter = 'recent', $includeDrafts = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp' ))
            ->from($this->getEntityName(), 'w')
            ->innerjoin('w.user', 'u')
            ->innerjoin('w.mainPicture', 'mp')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ('draft' == $filter && $includeDrafts) {
            $queryBuilder
                ->andWhere('w.isDraft = true')
            ;
        } elseif (!$includeDrafts) {
            $queryBuilder
                ->andWhere('w.isDraft = false')
            ;
        }

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByPlan(Plan $plan, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp', 'p' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->innerJoin('w.plans', 'p')
            ->where('w.isDraft = false')
            ->andWhere('p = :plan')
            ->setParameter('plan', $plan)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByHowto(Howto $howto, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp', 'h' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->innerJoin('w.howtos', 'h')
            ->where('w.isDraft = false')
            ->andWhere('h = :howto')
            ->setParameter('howto', $howto)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByWorkflow(Workflow $workflow, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp', 'wkf' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('w.mainPicture', 'mp')
            ->innerJoin('w.workflows', 'wkf')
            ->where('w.isDraft = false')
            ->andWhere('wkf = :workflow')
            ->setParameter('workflow', $workflow)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }
}
