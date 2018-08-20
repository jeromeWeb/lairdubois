<?php

namespace Ladb\CoreBundle\Repository\Core;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ladb\CoreBundle\Entity\Core\User;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class UserRepository extends AbstractEntityRepository
{

    public function createIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('a')->where('a.enabled = true');   // FOSElasticaBundle bug -> use 'a'
    }

    /////

    public function countDonors()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'COUNT(u.id)' ))
            ->from($this->getEntityName(), 'u')
            ->leftJoin('u.meta', 'm')
            ->where('m.donationCount > 0')
            ->andWhere('u.enabled = 1')
        ;

        try {
            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return 0;
        }
    }

    /////

    public function findByIds(array $ids)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'u', 'av', 'm' ))
            ->from($this->getEntityName(), 'u')
            ->innerJoin('u.avatar', 'av')
            ->leftJoin('u.meta', 'm')
            ->where($queryBuilder->expr()->in('u.id', $ids))
        ;

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /////

    public function findDonorsPagined($offset, $limit)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'u', 'a' ))
            ->from($this->getEntityName(), 'u')
            ->leftJoin('u.avatar', 'a')
            ->leftJoin('u.meta', 'm')
            ->where('m.donationCount > 0')
            ->andWhere('u.enabled = 1')
            ->orderBy('u.displayname', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return new Paginator($queryBuilder->getQuery());
    }

    public function findPagined($offset, $limit, $filter = 'recent', $isAdmin = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'u', 'a' ))
            ->from($this->getEntityName(), 'u')
            ->leftJoin('u.avatar', 'a')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ($filter != 'admin-not-enabled') {
            $queryBuilder
                ->andWhere('u.enabled = 1')
            ;
        }

        $this->_applyCommonFilter($queryBuilder, $filter, $isAdmin);

        return new Paginator($queryBuilder->getQuery());
    }

    private function _applyCommonFilter(&$queryBuilder, $filter, $isAdmin)
    {
        if ('contributors-all' == $filter) {
            $queryBuilder
                ->addOrderBy('u.contributionCount', 'DESC')
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        } elseif ('contributors-creations' == $filter) {
            $queryBuilder
                ->addOrderBy('u.publishedCreationCount', 'DESC')
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        } elseif ('contributors-plans' == $filter) {
            $queryBuilder
                ->addOrderBy('u.publishedPlanCount', 'DESC')
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        } elseif ('contributors-howtos' == $filter) {
            $queryBuilder
                ->addOrderBy('u.publishedHowtoCount', 'DESC')
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        } elseif ('contributors-workshops' == $filter) {
            $queryBuilder
                ->addOrderBy('u.publishedWorkshopCount', 'DESC')
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        } elseif ('contributors-comments' == $filter) {
            $queryBuilder
                ->addOrderBy('u.commentCount', 'DESC')
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        } elseif ('contributors-finds' == $filter) {
            $queryBuilder
                ->addOrderBy('u.publishedFindCount', 'DESC')
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        } elseif ('popular-followers' == $filter) {
            $queryBuilder
                ->addOrderBy('u.followerCount', 'DESC')
            ;
        } elseif ('popular-likes' == $filter) {
            $queryBuilder
                ->addOrderBy('u.recievedLikeCount', 'DESC')
            ;
        } elseif ('type-asso' == $filter) {
            $queryBuilder
                ->andWhere('u.accountType = ' . User::ACCOUNT_TYPE_ASSO)
            ;
        } elseif ('type-pro' == $filter) {
            $queryBuilder
                ->andWhere('u.accountType = ' . User::ACCOUNT_TYPE_PRO)
            ;
        } elseif ('type-hobbyist' == $filter) {
            $queryBuilder
                ->andWhere('u.accountType = ' . User::ACCOUNT_TYPE_HOBBYIST)
            ;
        } elseif ('social-facebook' == $filter) {
            $queryBuilder
                ->andWhere('u.facebook IS NOT NULL')
            ;
        } elseif ('social-twitter' == $filter) {
            $queryBuilder
                ->andWhere('u.twitter IS NOT NULL')
            ;
        } elseif ('social-googleplus' == $filter) {
            $queryBuilder
                ->andWhere('u.googleplus IS NOT NULL')
            ;
        } elseif ('social-youtube' == $filter) {
            $queryBuilder
                ->andWhere('u.youtube IS NOT NULL')
            ;
        } elseif ('social-vimeo' == $filter) {
            $queryBuilder
                ->andWhere('u.vimeo IS NOT NULL')
            ;
        } elseif ('social-dailymotion' == $filter) {
            $queryBuilder
                ->andWhere('u.dailymotion IS NOT NULL')
            ;
        } elseif ('social-pinterest' == $filter) {
            $queryBuilder
                ->andWhere('u.pinterest IS NOT NULL')
            ;
        } elseif ('social-instagram' == $filter) {
            $queryBuilder
                ->andWhere('u.instagram IS NOT NULL')
            ;
        } elseif ('admin-not-enabled' == $filter && $isAdmin) {
            $queryBuilder
                ->andWhere('u.enabled = 0')
            ;
        } elseif ('admin-not-email-confirmed' == $filter && $isAdmin) {
            $queryBuilder
                ->andWhere('u.emailConfirmed = 0')
            ;
        } else {
            $queryBuilder
                ->addOrderBy('u.createdAt', 'DESC')
            ;
        }
    }

    public function findGeocoded($filter = 'recent', $isAdmin = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'u', 'a' ))
            ->from($this->getEntityName(), 'u')
            ->leftJoin('u.avatar', 'a')
            ->where('u.latitude IS NOT NULL')
            ->andWhere('u.longitude IS NOT NULL')
        ;

        if (!$isAdmin) {
            $queryBuilder
                ->andwhere('u.enabled = 1')
            ;
        }

        $this->_applyCommonFilter($queryBuilder, $filter, $isAdmin);

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }
}
