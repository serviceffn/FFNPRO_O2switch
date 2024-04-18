<?php

namespace App\Repository;

use App\Entity\Associations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Query;


class AssociationsRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Associations::class);
    }

    public function getAllAssociationIds()
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id');

        $query = $qb->getQuery();

        $result = $query->getResult();

        $associationIds = array_column($result, 'id');

        return $associationIds;
    }


    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Associations) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function search($mots = null)
    {
        $query = $this->createQueryBuilder('a');
        if ($mots != null) {
            $query->andWhere('MATCH_AGAINST(a.nom) AGAINST (:mots boolean)>0')
                ->setParameter('mots', $mots);
        }

        return $query->getQuery()->getResult();
    }

    public function searchCentre($term)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nom LIKE :nom')
            ->setParameter('nom', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }

    public function searchForm($result)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nom LIKE :nom ')
            ->setParameter('nom', '%' . $result . '%')
            ->getQuery()
            ->getResult();
    }

    public function findInactifAssoc()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT id,nom,initiale,type,email,is_active FROM Associations WHERE updated_at NOT LIKE CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery();
        return $resultSet->fetchAll();
    }

    public function countInactifAssoc(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Associations WHERE updated_at NOT LIKE CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery();
        return $resultSet->fetchOne();
    }

    public function findAllCentreActive()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.is_active = 1')
            ->getQuery()
            ->getResult();
    }

    public function findAllAlphabetic()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRegions($id)
    {
        return $this->createQueryBuilder('a')
            ->select()
            ->andWhere('a.region = :region')
            ->setParameter('region', $id)
            ->getQuery()
            ->getResult();
    }

    public function findCentreDateDESC()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.updated_at', 'DESC')
            ->andWhere('a.is_active = 1')
            ->getQuery()
            ->getResult();
    }

    public function filterTypeCentre()
    {
        return $this->createQueryBuilder('a')
            ->select()
            ->andWhere('a.type = :type')
            ->setParameter('type', 'Centre')
            ->getQuery()
            ->getResult();
    }

    public function filterTypeRegion()
    {
        return $this->createQueryBuilder('a')
            ->select()
            ->andWhere('a.type = :type')
            ->setParameter('type', 'Region')
            ->getQuery()
            ->getResult();
    }

    public function filterTypeAssociation()
    {
        return $this->createQueryBuilder('a')
            ->select()
            ->andWhere('a.type = :type')
            ->setParameter('type', 'Association')
            ->getQuery()
            ->getResult();
    }

    public function findAllCentreDesactivate()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.is_active = 0')
            ->getQuery()
            ->getResult();
    }

    public function findRegionIdByUserId($userId)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('r.id')
            ->leftJoin('a.region', 'r', Join::WITH, 'a.region = r.id')
            ->andWhere('a.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();

        return $qb->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    public function displayAssociations()
    {
        return $this->createQueryBuilder('a')
            ->where('a.type = :associationType')
            ->setParameter('associationType', 'Association')
            ->getQuery()
            ->getResult();

    }
    
    public function displayCentres()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.type = :centreType')
            ->setParameter('centreType', 'Centre')
            ->getQuery()
            ->getResult();
    }


    public function displayClubs()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.type = :clubType')
            ->setParameter('clubType', 'Club')
            ->getQuery()
            ->getResult();
    }

    public function getAffiliationNumber($associationId)
    {
        return $this->createQueryBuilder('a')
            ->select('a.affiliation_number')
            ->andWhere('a.id = :associationId')
            ->setParameter('associationId', $associationId)
            ->getQuery()
            ->getSingleScalarResult();
    }


}
