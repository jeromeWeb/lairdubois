<?php

namespace Ladb\CoreBundle\Repository\Workflow;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ladb\CoreBundle\Entity\Core\User;
use Ladb\CoreBundle\Entity\Howto\Howto;
use Ladb\CoreBundle\Entity\Wonder\Creation;
use Ladb\CoreBundle\Entity\Wonder\Plan;
use Ladb\CoreBundle\Entity\Wonder\Workshop;
use Ladb\CoreBundle\Entity\Workflow\Workflow;
use Ladb\CoreBundle\Model\HiddableInterface;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class WorkflowRepository extends AbstractEntityRepository
{

    /////

    public function getDefaultJoinOptions()
    {
        return array( array( 'inner', 'user', 'u' ), array( 'left', 'mainPicture', 'mp' ) );
    }

    /////

    public function findByIds(array $ids)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'uav', 'mp', 'cts', 'pls', 'wks', 'hws' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->innerJoin('u.avatar', 'uav')
            ->leftJoin('w.mainPicture', 'mp')
            ->leftJoin('w.creations', 'cts')
            ->leftJoin('w.plans', 'pls')
            ->leftJoin('w.howtos', 'hws')
            ->leftJoin('w.workshops', 'wks')
            ->where($queryBuilder->expr()->in('w.id', $ids))
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
                ->addOrderBy('w.viewCount', 'DESC')
            ;
        } elseif ('popular-likes' == $filter) {
            $queryBuilder
                ->addOrderBy('w.likeCount', 'DESC')
            ;
        } elseif ('popular-comments' == $filter) {
            $queryBuilder
                ->addOrderBy('w.commentCount', 'DESC');
        }
        $queryBuilder
            ->addOrderBy('w.changedAt', 'DESC')
        ;
    }

    public function findPagined($offset, $limit, $filter = 'all')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w' ))
            ->from($this->getEntityName(), 'w')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByUser(User $user, $offset, $limit, $filter = 'all')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w' ))
            ->from($this->getEntityName(), 'w')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByCreation(Creation $creation, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp', 'c' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->leftJoin('w.mainPicture', 'mp')
            ->innerJoin('w.creations', 'c')
            ->where('w.visibility = ' . HiddableInterface::VISIBILITY_PUBLIC)
            ->andWhere('c = :creation')
            ->setParameter('creation', $creation)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

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
            ->leftJoin('w.mainPicture', 'mp')
            ->innerJoin('w.plans', 'p')
            ->where('w.visibility = ' . HiddableInterface::VISIBILITY_PUBLIC)
            ->andWhere('p = :plan')
            ->setParameter('plan', $plan)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByWorkshop(Workshop $workshop, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp', 'wks' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->leftJoin('w.mainPicture', 'mp')
            ->innerJoin('w.workshops', 'wks')
            ->where('w.visibility = ' . HiddableInterface::VISIBILITY_PUBLIC)
            ->andWhere('wks = :workshop')
            ->setParameter('workshop', $workshop)
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
            ->leftJoin('w.mainPicture', 'mp')
            ->innerJoin('w.howtos', 'h')
            ->where('w.visibility = ' . HiddableInterface::VISIBILITY_PUBLIC)
            ->andWhere('h = :howto')
            ->setParameter('howto', $howto)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByInspiration(Workflow $inspiration, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp', 'i' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->leftJoin('w.mainPicture', 'mp')
            ->innerJoin('w.inspirations', 'i')
            ->where('w.visibility = ' . HiddableInterface::VISIBILITY_PUBLIC)
            ->andWhere('i = :inspiration')
            ->setParameter('inspiration', $inspiration)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByRebound(Workflow $rebound, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'w', 'u', 'mp', 'r' ))
            ->from($this->getEntityName(), 'w')
            ->innerJoin('w.user', 'u')
            ->leftJoin('w.mainPicture', 'mp')
            ->innerJoin('w.rebounds', 'r')
            ->where('w.visibility = ' . HiddableInterface::VISIBILITY_PUBLIC)
            ->andWhere('r = :rebound')
            ->setParameter('rebound', $rebound)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }
}
