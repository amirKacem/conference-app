<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Conference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 2;

    private const DAYS_BEFORE_REJECTED_REMOVAL = 7;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentPaginator(Conference $conference, int $offset): Paginator
    {
        $query =  $this->createQueryBuilder('c');

        $query->andWhere('c.conference = :conference')
        ->andWhere($query->expr()->eq('c.state', ':state'))
        ->setParameter('conference', $conference)
        ->setParameter('state', "published")
        ->orderBy('c.createdAt', 'DESC')
        ->setMaxResults(self::PAGINATOR_PER_PAGE)
        ->setFirstResult($offset)
        ->getQuery();

        return new Paginator($query);
    }

    public function countOldRejected(): int
    {
        $qb = $this->getOldRejectedQueryBuilder();
        $alias = $qb->getRootAliases()[0];
        return $qb->select(
            $qb->expr()->count($alias.'.id')
        )->getQuery()
        ->getSingleScalarResult();

    }


    public function deleteOldRejected(): int
    {
        return $this->getOldRejectedQueryBuilder()
               ->delete()
               ->getQuery()
               ->execute();
    }

    public function getOldRejectedQueryBuilder(): QueryBuilder
    {
        $qb =  $this->createQueryBuilder('c');

        return $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->eq('c.state', ':state_spam'),
                $qb->expr()->eq('c.state', ':state_rejected')
            )
        )
        ->andWhere(
            $qb->expr()->lt('c.createdAt', ':date')
        )
        ->setParameter('state_rejected', 'rejected')
        ->setParameter('state_spam', 'spam')
        ->setParameter('date', new \DateTimeImmutable(-self::DAYS_BEFORE_REJECTED_REMOVAL.' days'));
    }

}
