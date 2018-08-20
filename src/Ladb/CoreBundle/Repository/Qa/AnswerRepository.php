<?php

namespace Ladb\CoreBundle\Repository\Qa;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ladb\CoreBundle\Entity\Blog\Answer;
use Ladb\CoreBundle\Entity\Qa\Question;
use Ladb\CoreBundle\Repository\AbstractEntityRepository;

class AnswerRepository extends AbstractEntityRepository
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
            ->select(array( 'a', 'u' ))
            ->from($this->getEntityName(), 'a')
            ->innerJoin('a.user', 'u')
            ->where('a.id = :id')
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
            ->select(array( 'a', 'u', 'bbs' ))
            ->from($this->getEntityName(), 'a')
            ->innerJoin('a.user', 'u')
            ->innerJoin('a.bodyBlocks', 'bbs')
            ->where('a.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /////

    public function findByQuestion(Question $question, $sorter = 'score')
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select(array( 'a', 'u', 'bbs' ))
            ->from($this->getEntityName(), 'a')
            ->innerJoin('a.question', 'q')
            ->innerJoin('a.user', 'u')
            ->innerJoin('a.bodyBlocks', 'bbs')
            ->where('a.question = :question')
            ->setParameter('question', $question)
        ;

        if ('score' == $sorter) {
            $queryBuilder
                ->addOrderBy('a.isBestAnswer', 'DESC')
                ->addOrderBy('a.voteScore', 'DESC')
                ->addOrderBy('a.createdAt', 'DESC')
            ;
        } elseif ('older' == $sorter) {
            $queryBuilder
                ->addOrderBy('a.createdAt', 'ASC')
            ;
        } elseif ('recent' == $sorter) {
            $queryBuilder
                ->addOrderBy('a.createdAt', 'DESC')
            ;
        }

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }
}
