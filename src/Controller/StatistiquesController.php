<?php

namespace App\Controller;

use App\Entity\Associations;
use App\Entity\Users;
use App\Form\ContactType;
use App\Form\ExportType;
use App\Form\SearchUsersType;
use App\Form\UsersType;
use App\Form\modifyUserType;
use App\Form\SearchByAssociations;
use App\Repository\AssociationsRepository;
use App\Repository\UsersRepository;
use App\Services\QrCodeService;
use ContainerDsvu5Ut\PaginatorInterface_82dac15;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\ExportService;



/**
 * @Route("/statistiques")
 */
class StatistiquesController extends AbstractController
{
    /**
     * @Route("/", name="statistiques_index", methods={"GET","POST"})
     */
    public function index(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $id = $this->getUser()->getId();
        $users = $doctrine->getRepository(Users::class)->findAll();
        $masculin = $doctrine->getRepository(Users::class)->countHommeFFN();
        $feminin = $doctrine->getRepository(Users::class)->countFemmeFFN();
        $ffn = $doctrine->getRepository(Users::class)->countAllFFN();
        $adulteFFN = $doctrine->getRepository(Users::class)->countAdulteFFN();
         $allAssoc = $doctrine->getRepository(Users::class)->countAllAssoc($id);
        $regionId = $doctrine->getRepository(Associations::class)->findRegionIdByUserId($id);
        $usersByRegionAndYear = $doctrine->getRepository(Users::class)->getUsersByRegionAndCurrentYear($id, $regionId);
        $hommeAssoc = $doctrine->getRepository(Users::class)->countHommeAssoc($id);
        $femmeAssoc = $doctrine->getRepository(Users::class)->countFemmeAssoc($id);
        $adulteAssoc = $doctrine->getRepository(Users::class)->countAdulteAssoc($id);

        $usersYear = $doctrine->getRepository(Users::class)->findAllYear();
        $usersYearDirect = $doctrine->getRepository(Users::class)->findAllYearDirect();
        $adulteYearDirect = $doctrine->getRepository(Users::class)->findAdulteYearDirect();
        $countAllMinorForTheActualYear = $doctrine->getRepository(Users::class)->countAllMinorsForTheActualYear();

        $regionYear = $doctrine->getRepository(Users::class)->findAllRegionYear(); #fait
        $adulteYearRegion = $doctrine->getRepository(Users::class)->findAdulteYearRegion();

        $usersYearAssoc = $doctrine->getRepository(Users::class)->findAllYearAssocYear(); #fait
        $adulteYearAssoc = $doctrine->getRepository(Users::class)->findAdulteYearAssoc(); #fait
        $usersYearCentre = $doctrine->getRepository(Users::class)->findAllYearCentre(); #fait
        $adulteYearCentre = $doctrine->getRepository(Users::class)->findAdulteYearCentre(); #fait
        $allMinorFromCentreForTheActualYear = $doctrine->getRepository(Users::class)->countAllMinorForTheActualYearFromCentre();

        $result = json_encode($usersYearCentre[0]);





        return $this->render('statistiques/index.html.twig', [
            'users' => $users,
            'masculin' => $masculin,
            'feminin' => $feminin,
            'ffn' => $ffn,
            'adulte' => $adulteFFN,
            'allAssocByAssociation' => count($usersByRegionAndYear),
            'allMinorByAssociationForTheActualYear' => $allMinorFromCentreForTheActualYear,
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
            'regionYear' => $regionYear,
            'adulteRegion' => $adulteYearRegion,
            'allMinorForTheActualYear' => $countAllMinorForTheActualYear,
            'allMinorFromCentreForTheActualYear' => $allMinorFromCentreForTheActualYear,
            'allAssoc' => $allAssoc
        ]);

    }


    /**
     * @Route("/associations", name="statistiques_associations_index", methods={"GET","POST"})
     */
    public function showAssocAll(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, 
    ManagerRegistry $doctrine, ExportService $exportService, 
    AssociationsRepository $associationsRepository): Response {
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $users = $doctrine->getRepository(Users::class)->countAll();
        $associationIds = $associationsRepository->getAllAssociationIds();

        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            
            $startingDate = $formData['dateDebut']->format('Y-m-d');
            $endingDate = $formData['dateFin']->format('Y-m-d');

            foreach ($associationIds as $associationId) {
                $exportService->exportAllAssoc($form, $usersRepository, $associationsRepository, $associationId, $startingDate, $endingDate);
            }        
        }

        return $this->render('statistiques/showAllAssoc.html.twig', [
            'users' => $users,
            'formDate' => $form->createView(),
        ]);
    }

    /**
     * @Route("/associations/{id}", name="statistiques_associations_id", methods={"GET","POST"})
     */
    public function showAssocId(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');


        $regionId = $doctrine->getRepository(Associations::class)->findRegionIdByUserId($id);
        $usersByRegionAndYear = $doctrine->getRepository(Users::class)->getUsersByRegionAndCurrentYear($id, $regionId);
        $users = $doctrine->getRepository(Users::class)->findAll();
        $masculin = $doctrine->getRepository(Users::class)->countHommeFFN();
        $feminin = $doctrine->getRepository(Users::class)->countFemmeFFN();
        $ffn = $doctrine->getRepository(Users::class)->countAllFFN();
        $adulteFFN = $doctrine->getRepository(Users::class)->countAdulteFFN();
         $allAssoc = $doctrine->getRepository(Users::class)->countAllAssoc($id);
        $hommeAssoc = $doctrine->getRepository(Users::class)->countHommeAssoc($id);
        $femmeAssoc = $doctrine->getRepository(Users::class)->countFemmeAssoc($id);
        $adulteAssoc = $doctrine->getRepository(Users::class)->countAdulteAssoc($id);
        $allMinorFromCentreForTheActualYear = $doctrine->getRepository(Users::class)->countAllMinorForTheActualYearFromCentre();




        return $this->render('statistiques/index.html.twig', [
            'users' => $users,
            'masculin' => $masculin,
            'feminin' => $feminin,
            'ffn' => $ffn,
            'adulte' => $adulteFFN,
            'allAssocByAssociation' => count($usersByRegionAndYear),
            'hommeAssoc' => $hommeAssoc,
            'femmeAssoc' => $femmeAssoc,
            'adulteAssoc' => $adulteAssoc,
            'allMinorFromCentreForTheActualYear' => $allMinorFromCentreForTheActualYear,
            'allAssoc' => $allAssoc

        ]);

    }

    /**
     * @Route("/associations", name="statistiques_by_associations", methods={"GET","POST"})
     */
    public function displayAllAssociations(AssociationsRepository $associationsRepository, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $associations = $doctrine->getRepository(Associations::class)->displayAssociations();
        // dump($associations);

        return $this->render('statistiques/statistiquesPerAssociations.html.twig', [
            'allAssociationList' => $associations

        ]);


    }


    /**
     * @Route("/statistiques/associations/centres", name="statistiques_by_centres", methods={"GET","POST"})
     */
    public function displayAllCentres(AssociationsRepository $associationsRepository, 
    Request $request, PaginatorInterface $paginator, 
    ManagerRegistry $doctrine, ExportService $exportService)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $centres = $doctrine->getRepository(Associations::class)->displayCentres();
        // dump($centres);

        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $startingDate = $formData['dateDebut']->format('Y-m-d');
            $endingDate = $formData['dateFin']->format('Y-m-d');

            $exportService->exportAllAssocByCentres($form, $startingDate, $endingDate);    
        }

        return $this->render('statistiques/statistiquesPerCentres.html.twig', [
            'allCentresList' => $centres,
            'formDate' => $form->createView(),
        ]);

    }

    /**
     * @Route("/statistiques/associations", name="statistiques_regions_licence_seller")
     */ 
    public function centreEmetteurNames(UsersRepository $userRepository, ExportService $exportService, Request $request)
    {
        $centreEmetteurNames = $userRepository->getCentreEmetteurNames();
        // dump($centreEmetteurNames);

        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            
            $startingDate = $formData['dateDebut']->format('Y-m-d');
            $endingDate = $formData['dateFin']->format('Y-m-d');


            $exportService->exportAllRegionsLicenceSeller( 
                $userRepository, 
                $startingDate,
                $endingDate
                );
        }


        return $this->render('statistiques/statistiquesLicenceSeller.html.twig', [
            'centreEmetteurNames' => $centreEmetteurNames,
            'formDate' => $form->createView()
        ]);
    }

}