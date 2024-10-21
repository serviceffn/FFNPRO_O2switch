<?php

namespace App\Repository;

use App\Entity\Prix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Prix|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prix|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prix[]    findAll()
 * @method Prix[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrixRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prix::class);
    }

    public function findAllPrices()
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.type_licence, p.prix')
            ->getQuery()
            ->getResult();
    }

    public function findPriceByTypeLicence(string $typeLicence)
    {
        return $this->createQueryBuilder('p')
            ->where('p.type_licence = :type_licence')
            ->setParameter('type_licence', $typeLicence)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllPricesSortedByPrix($order = 'ASC')
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.prix', $order)
            ->getQuery()
            ->getResult();
    }

    public function findPricesInRange(float $min, float $max)
    {
        return $this->createQueryBuilder('p')
            ->where('p.prix BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->getQuery()
            ->getResult();
    }
}
