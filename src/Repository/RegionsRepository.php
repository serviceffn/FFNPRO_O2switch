<?php

namespace App\Repository;

use App\Entity\Regions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Regions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Regions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Regions[]    findAll()
 * @method Regions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Regions::class);
    }

    public function countAllRegions($id): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT CONCAT(u.id, u.genre, TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE())))
        AS nombre_total_utilisateurs
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = :region_id
        WHERE r.id = :region_id";

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllRegionsYear($id): int
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = 'SELECT count(*) FROM Users WHERE region_id = :region_id and n_licence like CONCAT(year(CURRENT_DATE),"-%") ';
        // $sql = 'SELECT COUNT(*) FROM Users WHERE region_id = 13 AND YEAR(renouvellement_at) = 2023';
        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = :region_id
        WHERE r.id = :region_id
        AND u.n_licence LIKE CONCAT(YEAR(CURRENT_DATE()), '-%')";
        
        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllRegionsHommes($id): int
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs_masculins_majeurs
            FROM Users u
            JOIN Associations a ON u.centre_emetteur_id = a.id
            JOIN regions r ON a.region_id = :region_id
            WHERE r.id = :region_id
                AND u.genre = 'Masculin'
                AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) >= 18";
    
        $statement = $conn->prepare($sql);
    
        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result
    
        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }
    

    public function countAllRegionsHommesYear($id): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs_masculins_majeurs
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = :region_id
        WHERE r.id = 13
            AND u.genre = 'Masculin'
            AND u.n_licence LIKE CONCAT(YEAR(CURRENT_DATE()), '-%')
            AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) >= 18";

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllRegionsFemmes($id): int
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs_femmes_majeures
            FROM Users u
            JOIN Associations a ON u.centre_emetteur_id = a.id
            JOIN regions r ON a.region_id = :region_id
            WHERE r.id = :region_id
                AND u.genre = 'Feminin'
                AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) >= 18";
    
        $statement = $conn->prepare($sql);
    
        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result
    
        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }
    

    public function countAllRegionsFemmesYear($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs_masculins_majeurs
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = :region_id
        WHERE r.id = 13
            AND u.genre = 'Feminin'
            AND u.n_licence LIKE CONCAT(YEAR(CURRENT_DATE()), '-%')
            AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) >= 18";
        
        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAdulteRegionsYear($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE region_id = :region_id AND year(CURRENT_DATE)-year(anniversaire) >= 18 AND n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAdulteRegionsAll($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs_adultes
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = r.id
        WHERE r.id = :region_id
        AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) >= 18";

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllMinorsForTheYear($id): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs_masculins_majeurs
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = :region_id
        WHERE r.id = :region_id
        AND u.n_licence LIKE CONCAT(YEAR(CURRENT_DATE()), '-%')
        AND TIMESTAMPDIFF(YEAR, anniversaire, NOW()) < 18";


        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['region_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }
    public function countAllMinorsOfTheRegion($regionId): int
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "SELECT COUNT(u.id) AS nombre_total_utilisateurs_mineurs
            FROM Users u
            JOIN Associations a ON u.centre_emetteur_id = a.id
            JOIN regions r ON a.region_id = :region_id
            WHERE r.id = :region_id
            AND TIMESTAMPDIFF(YEAR, anniversaire, NOW()) < 18";
    
        $statement = $conn->prepare($sql);
    
        $resultSet = $statement->executeQuery(['region_id' => $regionId]);
    
        return $resultSet->fetchOne();
    }
    public function getAllAssociationsNamesFromTheRegion($regionId): int
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "SELECT DISTINCT a.nom AS nom_association 
        FROM Users u JOIN Associations a ON u.centre_emetteur_id = a.id 
        JOIN regions r ON a.region_id = r.id 
        WHERE r.id = :region_id 
        AND u.n_licence LIKE CONCAT(YEAR(CURRENT_DATE()), '-%');";
    
        $statement = $conn->prepare($sql);
    
        $resultSet = $statement->executeQuery(['region_id' => $regionId]);
    
        return $resultSet->fetchOne();
    }


    

    // /**
    //  * @return Regions[] Returns an array of Regions objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Regions
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
