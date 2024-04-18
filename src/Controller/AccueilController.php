<?php

namespace App\Controller;

use App\Entity\Associations;
use App\Entity\Users;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    /**
     * @Route("/accueil", name="accueil")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $this->getUser()->getId();
        $association = $doctrine->getRepository(Associations::class)->find($id);

        // Récupérer l'ID de la région associée à l'utilisateur
        $regionId = $doctrine->getRepository(Associations::class)->findRegionIdByUserId($id);

        // Utiliser getUsersByRegionAndCurrentYear avec l'ID de la région
        $usersByRegionAndYear = $doctrine->getRepository(Users::class)->getUsersByRegionAndCurrentYear($id, $regionId);

        $affiliationNumber = $doctrine->getRepository(Associations::class)->getAffiliationNumber($id);

        $users = $doctrine->getRepository(Users::class)->findAll();
        $masculin = $doctrine->getRepository(Users::class)->countHommeFFN();
        $feminin = $doctrine->getRepository(Users::class)->countFemmeFFN();
        $ffn = $doctrine->getRepository(Users::class)->countAllFFN();
        $adulteFFN = $doctrine->getRepository(Users::class)->countAdulteFFN();
        // $allAssoc = $doctrine->getRepository(Users::class)->countAllAssoc($id);
        $hommeAssoc = $doctrine->getRepository(Users::class)->countHommeAssoc($id);
        $femmeAssoc = $doctrine->getRepository(Users::class)->countFemmeAssoc($id);
        $adulteAssoc = $doctrine->getRepository(Users::class)->countAdulteAssoc($id);

        $usersYear = $doctrine->getRepository(Users::class)->findAllYear();
        $usersYearDirect = $doctrine->getRepository(Users::class)->findAllYearDirect();
        $adulteYearDirect = $doctrine->getRepository(Users::class)->findAdulteYearDirect();

        $usersYearAssoc = $doctrine->getRepository(Users::class)->findAllYearAssocYear(); #fait
        $adulteYearAssoc = $doctrine->getRepository(Users::class)->findAdulteYearAssoc(); #fait
        $usersYearCentre = $doctrine->getRepository(Users::class)->findAllYearCentre(); #fait
        $adulteYearCentre = $doctrine->getRepository(Users::class)->findAdulteYearCentre(); #fait
        $result = json_encode($usersYearCentre[0]);

        $countAllId = $doctrine->getRepository(Users::class)->countAllAssocId($id); #fait
        $countAllIdFemmes = $doctrine->getRepository(Users::class)->countAllAssocIdFemmes($id); #fait
        $countAllIdHommes = $doctrine->getRepository(Users::class)->countAllAssocIdHommes($id); #fait


        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'association' => $association,
            'countAllId' => $countAllId,
            'countAllIdFemmes' => $countAllIdFemmes,
            'countAllIdHommes' => $countAllIdHommes,
            'users' => $users,
            'masculin' => $masculin,
            'feminin' => $feminin,
            'ffn' => $ffn,
            'adulte' => $adulteFFN,
            'allAssoc' => count($usersByRegionAndYear),
            'hommeAssoc' => $hommeAssoc,
            'femmeAssoc' => $femmeAssoc,
            'adulteAssoc' => $adulteAssoc,
            'usersYear' => $usersYear,
            'usersYearDirect' => $usersYearDirect,
            'usersYearAssoc' => $usersYearAssoc,
            'adulteYearDirect' => $adulteYearDirect,
            'adulteYearAssoc' => $adulteYearAssoc,
            'adulteYearCentre' => $adulteYearCentre,
            'usersYearCentre' => $usersYearCentre,
        ]);
    }
}
