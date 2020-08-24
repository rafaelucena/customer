<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @param integer $hotelId
     * @param \DateTime $since
     * @param \DateTime $until
     */
    public function findByHotelAndDateRange(int $hotelId, \DateTime $since, \DateTime $until): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.hotel_id = :hotel')
            ->andWhere('r.created_date BETWEEN :first AND :second')
            ->setParameter('hotel', $hotelId)
            ->setParameter('first', $since->format('Y-m-d'))
            ->setParameter('second', $until->format('Y-m-d'))
            ->orderBy('r.created_date', 'ASC')
            ->setMaxResults(150)
            ->getQuery()
            ->getResult();
    }
}
