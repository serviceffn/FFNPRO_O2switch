<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\UsersFromAllYears;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use DoctrineExtensions\Query\Mysql\Greatest;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    // /**
    //  * @return Users[] Returns an array of Users objects
    //  */

    /**
     * @return Associations[] Returns an array of Associations objects
     */

    public function findLicenceByCentre($id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.imprimed_at,a.nom,a.prenom,a.n_licence,a.impression ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere('a.centre_emetteur = :val')
            ->setParameter('val', $id)
            ->orderBy("MostRecentDate", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findBlockUser($prenom, $nom, $anniversaire)
    {
        return $this->createQueryBuilder('a')
            ->select()
            ->andWhere('a.nom = :nom AND a.prenom = :prenom AND a.anniversaire = :anniversaire ')
            ->setParameter('prenom', $prenom)
            ->setParameter('nom', $nom)
            ->setParameter('anniversaire', $anniversaire)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countImprimed(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE is_imprimed = 0 AND n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $statement->fetchAll();
    }

    public function fetchAlll(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT * FROM Users ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchAll(); // fetch sur Result, pas sur Statement :eyes:
    }


    public function findLicenceDirectAll()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT entry_id,
        MAX(CASE WHEN meta_key = '6.3' THEN meta_value END) AS nom,
        MAX(CASE WHEN meta_key = '6.6' THEN meta_value END) AS prenom
        FROM azgr423dsf_gf_entry_meta GROUP BY entry_id DESC";


        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchAll(); // fetch sur Result, pas sur Statement :eyes:
    }


    public function findEntryId($entry_id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT * from azgr423dsf_gf_entry_meta where entry_id = :entry_id ";


        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['entry_id' => $entry_id]);

        return $resultSet->fetchAll(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function searchWordpress($searchTerm)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT 
                em1.entry_id,
                em1.meta_value AS nom,
                em2.meta_value AS prenom,
                em3.meta_value AS telephone,
                em4.meta_value AS birthday,
                em5.meta_value AS genre,
                em6.meta_value AS region,
                em7.meta_value AS adresse,
                em8.meta_value AS ville,
                em9.meta_value AS pays,
                em10.meta_value AS email,
                em11.meta_value AS qrcodeorimprimer

            FROM 
                azgr423dsf_gf_entry_meta em1
                JOIN azgr423dsf_gf_entry_meta em2 ON em2.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em3 ON em3.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em4 ON em4.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em5 ON em5.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em6 ON em6.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em7 ON em7.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em8 ON em8.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em9 ON em9.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em10 ON em10.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em11 ON em11.entry_id = em1.entry_id
            WHERE 
                em1.meta_key = '6.6'
                AND em2.meta_key = '6.3'
                AND em3.meta_key = '66'
                AND em4.meta_key = '9'
                AND em5.meta_key = '46.1'
                AND em6.meta_key = '119'
                AND em7.meta_key = '57'
                AND em8.meta_key = '88'
                AND em9.meta_key = '87'
                AND em10.meta_key = '75'
                AND em11.meta_key = '120'
                AND em1.meta_value LIKE :nom ;";

        $statement = $conn->prepare($sql);
        $resultSet = $statement->executeQuery(['nom' => $searchTerm]);

        return $resultSet->fetchAll();
    }


    public function searchWordpresss($searchTerm)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT 
                em1.entry_id ,
                em1.meta_value AS nom,
                em2.meta_value AS prenom,
                em3.meta_value AS telephone,
                em4.meta_value AS birthday,
                em5.meta_value AS genre,
                em6.meta_value AS region,
                em7.meta_value AS adresse,
                em8.meta_value AS ville,
                em9.meta_value AS pays,
                em10.meta_value AS email

            FROM 
                azgr423dsf_gf_entry_meta em1
                JOIN azgr423dsf_gf_entry_meta em2 ON em2.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em3 ON em3.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em4 ON em4.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em5 ON em5.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em6 ON em6.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em7 ON em7.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em8 ON em8.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em9 ON em9.entry_id = em1.entry_id
                JOIN azgr423dsf_gf_entry_meta em10 ON em10.entry_id = em1.entry_id
            WHERE 
                em1.meta_key = '6.6'
                AND em2.meta_key = '6.3'
                AND em3.meta_key = '66'
                AND em4.meta_key = '9'
                AND em5.meta_key = '46.1'
                AND em6.meta_key = '119'
                AND em7.meta_key = '57'
                AND em8.meta_key = '88'
                AND em9.meta_key = '87'
                AND em10.meta_key = '75'
                AND em1.entry_id LIKE :entry_id ;";

        $statement = $conn->prepare($sql);
        $resultSet = $statement->executeQuery(['entry_id' => $searchTerm]);

        return $resultSet->fetchAll();
    }





    public function printLicence($id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.imprimed_at, a.nom, a.prenom, a.impression , a.n_licence, a.nom, a.is_imprimed , b.nom as nomm, c.name, b.adresse, b.ville, b.zip, b.initiale, b.email_assoc')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->leftJoin(
                'App\Entity\Images',
                'c',
                'WITH',
                'c.associations = b.id'
            )
            ->andWhere('a.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function showQrCode($chaine)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.imprimed_at, a.nom, a.prenom, a.chaine, a.created_at, a.renouvellement_at, a.impression , a.n_licence, a.nom, a.is_imprimed , b.nom as nomm, c.name, b.adresse as adresseassoc, b.ville as villeassoc, b.zip as zipassoc, b.initiale, b.email_assoc as emailassoc')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->leftJoin(
                'App\Entity\Images',
                'c',
                'WITH',
                'c.associations = b.id'
            )
            ->andWhere('a.chaine = :val')
            ->setParameter('val', $chaine)
            ->getQuery()
            ->getResult()
        ;
    }

    public function showQrTrue($chaine)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT Users.id, Users.prenom, Users.chaine, Users.created_at, Users.email, Users.centre_emetteur_id as idassoc,  Users.renouvellement_at, Users.impression , Users.n_licence, Users.nom, Users.is_imprimed , Associations.nom as nomm, images.name, Associations.adresse as adresseassoc, Associations.ville as villeassoc, Associations.zip as zipassoc, Associations.initiale, Associations.email_assoc as emailassoc FROM Users LEFT JOIN Associations  ON Associations.id = Users.centre_emetteur_id LEFT JOIN images  ON images.associations_id = Associations.id WHERE Users.chaine = :chaine ';

        $statement = $conn->prepare($sql);

        // Vérifier si la préparation de la requête a réussi
        if ($statement === false) {
            throw new \Exception("Erreur de préparation de la requête SQL");
        }

        $result = $statement->execute(['chaine' => $chaine]);

        // Vérifier si l'exécution de la requête a réussi
        if ($result === false) {
            throw new \Exception("Erreur lors de l'exécution de la requête SQL");
        }

        // Utiliser fetch au lieu de fetchAll
        return $statement->fetchAll();
    }


    public function sortAlphabetic($id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.imprimed_at,a.nom,a.prenom,a.n_licence ,a.is_imprimed ,a.impression , a.created_at,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere('a.centre_emetteur = :val')
            ->setParameter('val', $id)
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByChaine($chaine)
    {
        return $this->createQueryBuilder('a')
            ->select('a.chaine,a.id,a.imprimed_at,a.nom,a.prenom,a.n_licence ,a.is_imprimed ,a.impression ,a.anniversaire, a.created_at,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere('a.chaine = :val')
            ->setParameter('val', $chaine)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countAll()
    {
        return $this->createQueryBuilder('a')
            ->select('b.id,b.nom , count(a.id) as NB')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->groupBy('b.id')
            ->orderBy('b.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }




    // ANCIENNE FONCTION , IL FAUDRAIT RETROUVER LE MOYEN DE CURRENT DATE DANS LE DQL
    // public function sortByAssociation($id)
    // {
    //     return $this->createQueryBuilder('a')
    //         ->select('a.id,a.nom,a.prenom,a.n_licence ,a.created_at,b.nom as nomm,a.is_imprimed,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
    //         ->leftJoin(
    //             'App\Entity\Associations',
    //             'b',
    //             'WITH',
    //             'b.id = a.centre_emetteur'
    //         )
    //         ->andWhere('a.centre_emetteur = :val AND a.is_imprimed = 0 AND a.n_licence LIKE CONCAT(year(CURRENT_DATE),"-%") ')
    //         ->setParameter('val', $id)
    //         ->orderBy('MostRecentDate', 'DESC')
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }

    public function sortByAssociation($id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT Users.id,Users.imprimed_at,Users.nom,Users.prenom,Users.impression,Users.n_licence,Users.created_at,Associations.nom as nomm,Users.is_imprimed,(CASE WHEN Users.renouvellement_at > Users.created_at THEN Users.renouvellement_at ELSE Users.created_at END) AS MostRecentDate FROM Users LEFT JOIN Associations ON Associations.id = Users.centre_emetteur_id WHERE centre_emetteur_id = "' . $id . '" and Users.is_imprimed = 0 AND Users.n_licence LIKE CONCAT(year(CURRENT_DATE),"-%") ORDER BY MostRecentDate DESC';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchAll(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function findLicenceByDESC()
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.imprimed_at,a.nom,a.prenom,a.n_licence,a.impression  ,a.created_at, a.is_imprimed ,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            // ->select('substr(MostRecentDate,0,10)')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->orderBy("MostRecentDate", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }


    // // $dateFin = $dateFin->modify('+1 day');
    // public function findByDateDebutAndFin($dateDebut, $dateFin)
    // {
    //     $currentYear = (new \DateTime())->format('Y');

    //     return $this->createQueryBuilder('a')
    //     ->select('a.id, a.imprimed_at, a.nom, a.prenom, a.n_licence, a.impression, a.agree_terms, a.created_at, b.nom as nomm, a.anniversaire, a.is_imprimed, a.genre, a.telephone, a.email, a.adresse, a.complement, a.zip, a.ville, a.pays, a.renouvellement_at, (CASE WHEN a.created_at > a.renouvellement_at THEN a.created_at ELSE a.renouvellement_at END) AS MostRecentDate')
    //     ->leftJoin(
    //         'App\Entity\Associations',
    //         'b',
    //         'WITH',
    //         'b.id = a.centre_emetteur'
    //     )
    //     ->andWhere('a.n_licence LIKE :licencePattern')
    //     ->andWhere('(a.renouvellement_at >= :debut AND a.renouvellement_at <= :fin) OR (a.created_at >= :debut AND a.created_at <= :fin)')
    //     ->setParameter('licencePattern', $currentYear . '-%')
    //     ->setParameter('debut', $dateDebut->format('Y-m-d H:i:s'))
    //     ->setParameter('fin', $dateFin->modify('+1 day')->format('Y-m-d H:i:s'))
    //     ->orderBy("MostRecentDate", "ASC")
    //     ->getQuery()
    //     ->getResult();
    // }
    
    public function findByDateDebutAndFin($dateDebut, $dateFin)
    {
        $currentYear = (new \DateTime())->format('Y');
        
        $sql = "SELECT a.id, a.imprimed_at, a.nom, a.prenom, a.n_licence, a.impression, a.agree_terms, a.created_at, b.nom as nomm, a.anniversaire, a.is_imprimed, a.genre, a.telephone, a.email, a.adresse, a.complement, a.zip, a.ville, a.pays, a.renouvellement_at, 
        (CASE WHEN a.created_at > a.renouvellement_at THEN a.created_at ELSE a.renouvellement_at END) AS MostRecentDate
        FROM Users a
        LEFT JOIN Associations b ON b.id = a.centre_emetteur_id
        WHERE a.n_licence LIKE :licencePattern
        AND ((a.renouvellement_at BETWEEN :debut AND :fin) OR (a.created_at BETWEEN :debut AND :fin))
        ORDER BY MostRecentDate ASC";
    
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute([
            'licencePattern' => $currentYear . '-%',
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->modify('+1 day')->format('Y-m-d H:i:s')
        ]);
    
        return $stmt->fetchAll();
    }
    
    public function findImpressionAssoc()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT Associations.id as idAssoc, Associations.nom, COUNT(Users.id) as totalLicences
            FROM Users
            LEFT JOIN Associations ON Associations.id = Users.centre_emetteur_id
            WHERE Users.is_imprimed = 0 AND Users.n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            GROUP BY Associations.id, Associations.nom;';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function findAssocRegions()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(Users.id) as c , Associations.id FROM `Users` LEFT JOIN Associations ON Associations.id = Users.centre_emetteur_id group by Associations.nom';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery();

        return $resultSet->fetchAll();
    }


    public function findByDateDebutAndFinAssoc($dateDebut, $dateFin, $id)
    {

        $dateFin = $dateFin->modify('+1 day');
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence,a.impression  ,a.created_at,b.id,b.nom as nomm,a.anniversaire,a.genre,a.telephone,a.email,a.adresse,a.complement,a.zip,a.ville,a.pays,a.created_at,a.renouvellement_at,(CASE WHEN a.created_at  >= a.renouvellement_at THEN a.created_at ELSE a.renouvellement_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere('a.created_at BETWEEN :debut AND :fin OR a.renouvellement_at BETWEEN :debut AND :fin ')
            ->andWhere('b.id = :val')
            ->setParameter('debut', $dateDebut->format('Y-m-d'))
            ->setParameter('fin', $dateFin->format('Y-m-d'))
            ->setParameter('val', $id)
            ->orderBy("MostRecentDate", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }



    public function findByLicenceAll($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT n_licence FROM Users WHERE id = :id';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function findByLicence($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT SUBSTR(n_licence,5,7) FROM Users WHERE id = :id';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function searchAll($term)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence,a.impression  ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere('a.n_licence LIKE :licence')
            ->setParameter('licence', '%' . $term . '%')
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    public function searchNomAll($term)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence,a.impression  ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere("CONCAT_WS(' ',a.nom,a.prenom) like :nom OR CONCAT_WS(' ',a.prenom,a.nom) like :nom ")
            ->setParameter('nom', '%' . $term . '%')
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchNomAnnivAll($term, $id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.impression ,a.anniversaire,a.n_licence ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere("CONCAT_WS(' ',a.nom,a.prenom,a.anniversaire) like :nom AND a.centre_emetteur = :id OR CONCAT_WS(' ',a.prenom,a.nom,a.anniversaire) like :nom AND a.centre_emetteur = :id OR CONCAT_WS(' ',a.prenom,a.nom,a.anniversaire) like :nom AND a.centre_emetteur = :id ")
            ->setParameter('nom', '%' . $term . '%')
            ->setParameter('id', $id)
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchNomAnnivAllFFN($term)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.impression ,a.anniversaire,a.n_licence ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere("CONCAT_WS(' ',a.nom,a.prenom,a.anniversaire) like :nom OR CONCAT_WS(' ',a.prenom,a.nom,a.anniversaire) like :nom  OR CONCAT_WS(' ',a.prenom,a.nom,a.anniversaire) like :nom  ")
            ->setParameter('nom', '%' . $term . '%')
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchNomAllFFN($term)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence ,a.impression ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere("a.nom like :nom ")
            ->setParameter('nom', '%' . $term . '%')
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchNomAllASSOC($term, $id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence ,a.impression ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere("a.nom like :nom AND a.centre_emetteur = :id")
            ->setParameter('nom', '%' . $term . '%')
            ->setParameter('id', $id)
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function search($term, $id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence ,a.impression ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere('a.n_licence LIKE :licence AND a.centre_emetteur = :id')
            ->setParameter('licence', '%' . $term . '%')
            ->setParameter('id', $id)
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    public function searchNom($term, $id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence,a.impression  ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere("CONCAT_WS(' ',a.nom,a.prenom) like :nom OR CONCAT_WS(' ',a.prenom,a.nom) like :nom ")
            ->andWhere("a.centre_emetteur = :id")
            ->setParameter('nom', '%' . $term . '%')
            ->setParameter('id', $id)
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    public function searchCentre($term, $id)
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.nom,a.prenom,a.imprimed_at,a.n_licence,a.impression  ,a.created_at,a.is_imprimed,b.nom as nomm,(CASE WHEN a.renouvellement_at > a.created_at THEN a.renouvellement_at ELSE a.created_at END) AS MostRecentDate')
            ->leftJoin(
                'App\Entity\Associations',
                'b',
                'WITH',
                'b.id = a.centre_emetteur'
            )
            ->andWhere('a.nom LIKE :nom')
            ->setParameter('nom', '%' . $term . '%')
            ->andWhere('a.centre_emetteur = :id')
            ->setParameter('id', $id)
            ->orderBy('a.prenom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function updateIsImprimed()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'UPDATE Users SET is_imprimed = false';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllAssoc($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE centre_emetteur_id = :centre_emetteur_id AND n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['centre_emetteur_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }


    public function getUsersByRegionAndCurrentYear($centreEmetteurId, $regionId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT *, Associations.region_id
                FROM Users
                JOIN Associations ON Users.centre_emetteur_id = Associations.id
                WHERE Users.centre_emetteur_id = :centre_emetteur_id
                    AND Users.n_licence LIKE :year_pattern
                    AND Associations.region_id = :region_id';

        // dump($sql);
        // dump([
        //     'centre_emetteur_id' => $centreEmetteurId,
        //     'year_pattern' => '%' . date('Y') . '%',
        //     'region_id' => $regionId,
        // ]);

        $statement = $conn->prepare($sql);
        $statement->execute([
            'centre_emetteur_id' => $centreEmetteurId,
            'year_pattern' => '%' . date('Y') . '%',
            'region_id' => $regionId,
        ]);

        return $statement->fetchAllAssociative();
    }




    public function countAllAssocId($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE centre_emetteur_id = :centre_emetteur_id';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['centre_emetteur_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllAssocIdFemmes($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE centre_emetteur_id = :centre_emetteur_id and genre = "Feminin"';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['centre_emetteur_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllAssocIdHommes($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE centre_emetteur_id = :centre_emetteur_id and genre = "Masculin"';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['centre_emetteur_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countHommeAssoc($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE centre_emetteur_id = :centre_emetteur_id AND genre = "Masculin" AND n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['centre_emetteur_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countFemmeAssoc($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE centre_emetteur_id = :centre_emetteur_id AND genre = "Feminin" AND n_licence  like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['centre_emetteur_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAdulteAssoc($id): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users WHERE centre_emetteur_id = :centre_emetteur_id AND year(CURRENT_DATE)-year(anniversaire) >= 18 AND n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['centre_emetteur_id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }


    public function findAllYear(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        // $sql = "SELECT COUNT(*) AS count_matching_licence FROM Users WHERE n_licence LIKE CONCAT('%', YEAR(CURDATE()), '%')";
        $sql = "SELECT COUNT(DISTINCT u.id) AS total_licences_annee_actuelle
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = r.id
        WHERE u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')";


        // $sql = 'SELECT COUNT(*) as total_users FROM Users WHERE (created_at BETWEEN "2023-01-01" AND "2023-11-13") OR (renouvellement_at BETWEEN "2023-01-01" AND "2023-11-13" AND renouvellement_at IS NOT NULL)';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function findAllYearDirect(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT u.id) AS total_utilisateurs_centre_94
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = r.id
        WHERE u.centre_emetteur_id = 94
        AND u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')";

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function findAdulteYearDirect(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT u.id) AS total_utilisateurs_licence_direct_94_majeurs
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = r.id
        WHERE u.centre_emetteur_id = 94
        AND u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')
        AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) >= 18";


        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllMinorsForTheActualYear(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT u.id) AS total_utilisateurs_mineurs_centre_94
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = r.id
        WHERE u.centre_emetteur_id = 94
        AND u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')
        AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) < 18";

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery();

        return $resultSet->fetchOne();
    }



    public function findAdulteYearAssoc(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
        LEFT JOIN Associations ON Users.centre_emetteur_id = Associations.id 
        WHERE centre_emetteur_id != 94 
        AND year(CURRENT_DATE)-year(Users.anniversaire) >= 18 
        AND Associations.type = "Association" 
        AND Users.n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllMinorForTheActualYearFromAssociation()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT u.id) AS nombre_utilisateurs_mineurs
                FROM Users u
                JOIN Associations a ON u.centre_emetteur_id = a.id
                JOIN regions r ON a.region_id = r.id
                WHERE a.type = 'Association'
                AND u.centre_emetteur_id != 94
                AND u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')
                AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) < 18";


        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery();

        return $resultSet->fetchOne();
    }

    public function findAdulteYearCentre(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*)  FROM Users LEFT JOIN Associations ON Users.centre_emetteur_id = Associations.id WHERE centre_emetteur_id != 94 AND year(CURRENT_DATE)-year(Users.anniversaire) >= 18 AND Associations.type = "Centre" AND Users.n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllMinorForTheActualYearFromCentre()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT u.id) AS nombre_utilisateurs_mineurs
                FROM Users u
                JOIN Associations a ON u.centre_emetteur_id = a.id
                JOIN regions r ON a.region_id = r.id
                WHERE a.type = 'Centre'
                AND u.centre_emetteur_id != 94
                AND u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')
                AND TIMESTAMPDIFF(YEAR, u.anniversaire, CURDATE()) < 18";


        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery();

        return $resultSet->fetchOne();

    }

    public function findAllYearCentre(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT u.id) AS nombre_utilisateurs_association
        FROM Users u
        JOIN Associations a ON u.centre_emetteur_id = a.id
        JOIN regions r ON a.region_id = r.id
        WHERE a.type = 'Centre'
        AND u.centre_emetteur_id != 94
        AND u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')";

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function findAllRegionYear(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) as nb FROM Users LEFT JOIN Associations ON Users.centre_emetteur_id = Associations.id WHERE  centre_emetteur_id != 94  AND Associations.type = "Region" AND Users.n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function findAdulteYearRegion(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users LEFT JOIN Associations ON Users.centre_emetteur_id = Associations.id  WHERE Users.centre_emetteur_id != 94 AND year(CURRENT_DATE)-year(anniversaire) >= 18 AND Associations.type = "Region" AND Users.n_licence like CONCAT(year(CURRENT_DATE),"-%") ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function findAllYearAssocYear(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(DISTINCT u.id) AS nombre_utilisateurs_association 
        FROM Users u JOIN Associations a ON u.centre_emetteur_id = a.id 
        JOIN regions r ON a.region_id = r.id 
        WHERE a.type = 'Association' 
        AND u.centre_emetteur_id != 94 
        AND u.n_licence LIKE CONCAT(YEAR(CURDATE()), '-%')";

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }


    public function countAdulteFFN()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users where year(CURRENT_DATE)-year(anniversaire) >= 18';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countHommeFFN()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users where genre = "Masculin"';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAllFFN()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(*) FROM Users';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countFemmeFFN()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users where genre = "Feminin" ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function updateIsImprimedId($id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'UPDATE Users SET is_imprimed = true , impression = true , imprimed_at = now() WHERE id = :id ';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function updateIsImprimedIdFalse($id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'UPDATE Users SET is_imprimed = false WHERE id = :id';

        $statement = $conn->prepare($sql);

        $resultSet = $statement->executeQuery(['id' => $id]); // instance de type Result

        return $resultSet->fetchOne(); // fetch sur Result, pas sur Statement :eyes:
    }

    public function countAdulteAssocForCsvExport($id, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :centre_emetteur_id 
            AND year(CURRENT_DATE)-year(anniversaire) >= 18
            AND n_licence like CONCAT(year(CURRENT_DATE),"-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'centre_emetteur_id' => $id,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }


    public function countEnfantsAssocForCsvExport($id, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :centre_emetteur_id 
            AND year(CURRENT_DATE) - year(anniversaire) < 18
            AND n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'centre_emetteur_id' => $id,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }


    public function countHommeAssocForCsvExport($id, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :centre_emetteur_id 
            AND genre = "Masculin" 
            AND n_licence like CONCAT(year(CURRENT_DATE),"-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'centre_emetteur_id' => $id,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }


    public function countFemmeAssocForCsvExport($id, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :centre_emetteur_id 
            AND genre = "Feminin" 
            AND n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'centre_emetteur_id' => $id,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }


    public function countAllAssocForCsvExport($id, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :centre_emetteur_id 
            AND n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'centre_emetteur_id' => $id,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }



    public function getCentreEmetteurNames()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT DISTINCT id, nom FROM Associations
        WHERE id IN (168, 189, 199, 204, 210, 213, 214, 218, 221)';
        $statement = $conn->prepare($sql);
        $statement->execute();

        return $statement->fetchAllAssociative();
    }


    public function countAdulteAssocForRegionLicenceSeller($idRegion, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :idRegion           
            AND year(CURRENT_DATE) - year(anniversaire) >= 18
            AND n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'idRegion' => $idRegion,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }


    public function countEnfantsAssocForRegionLicenceSeller($idRegion, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :idRegion      
            AND year(CURRENT_DATE) - year(anniversaire) < 18
            AND n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'idRegion' => $idRegion,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }

    public function countHommeAssocForRegionLicenceSeller($idRegion, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :idRegion             
            AND genre = "Masculin" 
            AND n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'idRegion' => $idRegion,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }


    public function countFemmeAssocForRegionLicenceSeller($idRegion, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT count(*) FROM Users 
            WHERE centre_emetteur_id = :idRegion 
            AND genre = "Feminin" 
            AND n_licence LIKE CONCAT(year(CURRENT_DATE), "-%")
            AND (
                (DATE(created_at) BETWEEN :start_date AND :end_date)
                OR
                (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
            )';

        $statement = $conn->prepare($sql);
        $parameters = [
            'idRegion' => $idRegion,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }

    public function countAllAssocForRegionLicenceSeller($idRegion, $startingDate, $endingDate)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(*) FROM Users
        WHERE centre_emetteur_id = :idRegion 
        AND n_licence LIKE CONCAT(YEAR(CURRENT_DATE), '-%')
        AND (
            (DATE(created_at) BETWEEN :start_date AND :end_date)
            OR
            (DATE(renouvellement_at) BETWEEN :start_date AND :end_date)
        )";

        $statement = $conn->prepare($sql);
        $parameters = [
            'idRegion' => $idRegion,
            'start_date' => $startingDate,
            'end_date' => $endingDate,
        ];

        $resultSet = $statement->executeQuery($parameters);

        return $resultSet->fetchOne();
    }

}
