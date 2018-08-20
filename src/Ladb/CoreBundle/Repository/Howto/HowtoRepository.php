<?php

namespace Ladb\CoreBundle\Repository\Howto;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ladb\CoreBundle\Entity\Knowledge\Provider;
use Ladb\CoreBundle\Entity\Core\User;
use Ladb\CoreBundle\Entity\Wonder\Creation;
use Ladb\CoreBundle\Entity\Wonder\Plan;
use Ladb\CoreBundle\Entity\Wonder\Workshop;
use Ladb\CoreBundle\Entity\Workflow\Workflow;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class HowtoRepository extends AbstractEntityRepository
{

    /////

    public function getDefaultJoinOptions()
    {
        return array( array( 'inner', 'user', 'u' ), array( 'left', 'mainPicture', 'mp' ) );
    }

    /////

    public function findOneByIdJoinedOnOptimized($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'h', 'u', 'mp', 'ats', 'abbs'/*, 'pls', 'cts', 'wfs', 'wks'*/ ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->innerJoin('h.mainPicture', 'mp')
            ->leftJoin('h.articles', 'ats')
            ->leftJoin('ats.bodyBlocks', 'abbs')
//			->leftJoin('h.plans', 'pls')		// Memory limit exceded
//			->leftJoin('h.creations', 'cts')
//			->leftJoin('h.workflows', 'wfs')
//			->leftJoin('h.workshops', 'wks')
            ->where('h.id = :id')
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
            ->select(array( 'h', 'u', 'mp' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->where('h.isDraft = false')
            ->andWhere('h.user = :user')
            ->orderBy('h.id', 'ASC')
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
            ->select(array( 'h', 'u', 'mp' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->where('h.isDraft = false')
            ->andWhere('h.user = :user')
            ->orderBy('h.id', 'DESC')
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
            ->select(array( 'h', 'u', 'mp' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->where('h.isDraft = false')
            ->andWhere('h.user = :user')
            ->andWhere('h.id < :id')
            ->orderBy('h.id', 'DESC')
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
            ->select(array( 'h', 'u', 'mp' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->where('h.isDraft = false')
            ->andWhere('h.user = :user')
            ->andWhere('h.id > :id')
            ->orderBy('h.id', 'ASC')
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
            ->select(array( 'h', 'u', 'mp', 'sp' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.spotlight', 'sp')
            ->where($queryBuilder->expr()->in('h.id', $ids))
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
            ->select(array( 'h', 'u', 'mp', 't' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.tags', 't')
            ->where('h.isDraft = false')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ('followed' == $filter) {
            $queryBuilder
                ->innerJoin('u.followers', 'f', 'WITH', 'f.user = :filterParam:')
                ->setParameter('filterParam', $filterParam);
        }

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    private function _applyCommonFilter(&$queryBuilder, $filter)
    {
        if ('popular-views' == $filter) {
            $queryBuilder
                ->addOrderBy('h.viewCount', 'DESC')
            ;
        } elseif ('popular-likes' == $filter) {
            $queryBuilder
                ->addOrderBy('h.likeCount', 'DESC')
            ;
        } elseif ('popular-comments' == $filter) {
            $queryBuilder
                ->addOrderBy('h.commentCount', 'DESC')
            ;
        } elseif ('popular-spotlights' == $filter) {
            $queryBuilder
                ->innerJoin('h.spotlight', 's')
                ->andWhere('s.enabled = 1')
                ->addOrderBy('s.createdAt', 'DESC')
            ;
        } elseif ('content-creations' == $filter) {
            $queryBuilder
                ->addOrderBy('h.creationCount', 'DESC')
            ;
        } elseif ('content-plans' == $filter) {
            $queryBuilder
                ->addOrderBy('h.planCount', 'DESC')
            ;
        } elseif ('content-workshops' == $filter) {
            $queryBuilder
                ->addOrderBy('h.workshopCount', 'DESC')
            ;
        } elseif ('content-videos' == $filter) {
            $queryBuilder
                ->addOrderBy('h.bodyBlockVideoCount', 'DESC')
            ;
        } elseif ('content-wip' == $filter) {
            $queryBuilder
                ->andWhere('h.isWorkInProgress = 1')
            ;
        } elseif ('license-by' == $filter) {
            $queryBuilder
                ->innerJoin('h.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 1')
            ;
        } elseif ('license-by-nc' == $filter) {
            $queryBuilder
                ->innerJoin('h.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 0')
            ;
        } elseif ('license-by-nc-nd' == $filter) {
            $queryBuilder
                ->innerJoin('h.license', 'l')
                ->andWhere('l.allowDerivs = 0')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 0')
            ;
        } elseif ('license-by-nc-sa' == $filter) {
            $queryBuilder
                ->innerJoin('h.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 1')
                ->andWhere('l.allowCommercial = 0')
            ;
        } elseif ('license-by-nd' == $filter) {
            $queryBuilder
                ->innerJoin('h.license', 'l')
                ->andWhere('l.allowDerivs = 0')
                ->andWhere('l.shareAlike = 0')
                ->andWhere('l.allowCommercial = 1')
            ;
        } elseif ('license-by-sa' == $filter) {
            $queryBuilder
                ->innerJoin('h.license', 'l')
                ->andWhere('l.allowDerivs = 1')
                ->andWhere('l.shareAlike = 1')
                ->andWhere('l.allowCommercial = 1')
            ;
        }
        $queryBuilder
            ->addOrderBy('h.changedAt', 'DESC');
    }

    public function findPaginedByUser(User $user, $offset, $limit, $filter = 'recent', $includeDrafts = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'h', 'u', 'mp', 't' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.tags', 't')
            ->where('h.user = :user')
            ->setParameter('user', $user)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ('draft' == $filter && $includeDrafts) {
            $queryBuilder
                ->andWhere('h.isDraft = true')
            ;
        } elseif (!$includeDrafts) {
            $queryBuilder
                ->andWhere('h.isDraft = false')
            ;
        }

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByPlan(Plan $plan, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'h', 'u', 'mp', 't', 'p' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.tags', 't')
            ->innerJoin('h.plans', 'p')
            ->where('h.isDraft = false')
            ->andWhere('p = :plan')
            ->setParameter('plan', $plan)
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
            ->select(array( 'h', 'u', 'mp', 't', 'c' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.tags', 't')
            ->innerJoin('h.creations', 'c')
            ->where('h.isDraft = false')
            ->andWhere('c = :creation')
            ->setParameter('creation', $creation)
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
            ->select(array( 'h', 'u', 'mp', 't', 'w' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.tags', 't')
            ->innerJoin('h.workflows', 'w')
            ->where('h.isDraft = false')
            ->andWhere('w = :workflow')
            ->setParameter('workflow', $workflow)
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
            ->select(array( 'h', 'u', 'mp', 't', 'w' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.tags', 't')
            ->innerJoin('h.workshops', 'w')
            ->where('h.isDraft = false')
            ->andWhere('w = :workshop')
            ->setParameter('workshop', $workshop)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPaginedByProvider(Provider $provider, $offset, $limit, $filter = 'recent')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'h', 'u', 'mp', 't', 'p' ))
            ->from($this->getEntityName(), 'h')
            ->innerJoin('h.user', 'u')
            ->leftJoin('h.mainPicture', 'mp')
            ->leftJoin('h.tags', 't')
            ->innerJoin('h.providers', 'p')
            ->where('h.isDraft = false')
            ->andWhere('p = :provider')
            ->setParameter('provider', $provider)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $this->_applyCommonFilter($queryBuilder, $filter);

        return new Paginator($queryBuilder->getQuery());
    }
}
