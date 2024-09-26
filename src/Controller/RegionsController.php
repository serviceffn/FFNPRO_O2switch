<?php

namespace App\Controller;

use App\Entity\Regions;
use App\Form\ExportType;
use App\Form\RegionsType;
use App\Repository\RegionsRepository;
use App\Repository\AssociationsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\ExportService;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/regions")
 */
class RegionsController extends AbstractController
{
    /**
     * @Route("/", name="regions_index", methods={"GET"})
     */
    public function index(Request $request, RegionsRepository $regionsRepository, ExportService $exportService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(ExportType::class);
        $formAssoc = $this->createForm(ExportType::class);
        return $this->render('regions/index.html.twig', [
            'regions' => $regionsRepository->findAll(),
            'form' => $form->createView(),
            'form_assoc' => $formAssoc->createView(),
        ]);
    }


    /**
     * @Route("/new", name="regions_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $region = new Regions();
        $form = $this->createForm(RegionsType::class, $region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($region);
            $entityManager->flush();

            return $this->redirectToRoute('regions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('regions/new.html.twig', [
            'region' => $region,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/export", name="regions_export", methods={"POST"})
     */
    public function export(Request $request, ExportService $exportService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $startingDate = $data['dateDebut'];
            $endingDate = $data['dateFin'];

            $exportService->exportAllRegions($startingDate, $endingDate);
        }

        return $this->redirectToRoute('regions_index');
    }
    /**
     * @Route("/export_assoc", name="regions_assoc_export", methods={"POST"})
     */
    public function exportRegionsAndAssoc(Request $request, ExportService $exportService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $startingDate = $data['dateDebut'];
            $endingDate = $data['dateFin'];

            $exportService->exportAllRegionsAndAsssoc($startingDate, $endingDate);
        }

        return $this->redirectToRoute('regions_index');
    }

// /**
//  * @Route("/regions/csv_region", name="csv_region")
//  */
// public function downloadCSV(Request $request, RegionsRepository $regionsRepository, ExportService $exportService)
// {
//     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

//     $form = $this->createForm(ExportType::class);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         // Récupérer les données du formulaire
//         $data = $form->getData();
//         $startingDate = $data['dateDebut'];
//         $endingDate = $data['dateFin'];

//         // Appel de la méthode d'export
//         $exportService->exportAllRegions($regionsRepository, $startingDate, $endingDate);

//         return $this->redirectToRoute('regions_index', [], Response::HTTP_SEE_OTHER);
//     }

//     return $this->render('regions/index.html.twig', [
//         'form' => $form->createView(),
//     ]);
// }


    /**
     * @Route("/{id}", name="regions_show", methods={"GET"})
     */
    public function show(Regions $region): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('regions/show.html.twig', [
            'region' => $region,
            'regions' => $region,
        ]);
    } 

    /**
     * @Route("/statistiques/{id}", name="statistiques_regions")
     */
    public function regionsStatistiques(RegionsRepository $regionsRepository,$id,PaginatorInterface $paginator ,AssociationsRepository $associationsRepository,Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        //,PaginatorInterface $paginator ,AssociationsRepository $associationsRepository,Request $request
        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);


        $regions = $regionsRepository->find($id);
        $countAllRegions = $regionsRepository->countAllRegions($id);   
        $countAllRegionsYear = $regionsRepository->countAllRegionsYear($id);   
        $countAllRegionsHommes = $regionsRepository->countAllRegionsHommes($id);  
        $countAllRegionsHommesYear = $regionsRepository->countAllRegionsHommesYear($id);        
        $countAllRegionsFemmes = $regionsRepository->countAllRegionsFemmes($id); 
        $countAllRegionsFemmesYear = $regionsRepository->countAllRegionsFemmesYear($id);       
        $countAdulteRegionsYear = $regionsRepository->countAdulteRegionsYear($id);     
        $countAdulteRegionsAll = $regionsRepository->countAdulteRegionsAll($id);
        // $countEnfantsRegion = $countAllRegions - $countAdulteRegionsAll;
        $countAllMinorsOfTheRegion = $regionsRepository->countAllMinorsOfTheRegion($id);
        // $countEnfantsRegionsYear = $countAllRegionsYear - $countAdulteRegionsYear;
        $countAllMinorsForTheYear = $regionsRepository->countAllMinorsForTheYear($id);
        $centre = $associationsRepository->findByRegions($id); 
        $associationsPage = $paginator->paginate($centre,$request->query->getInt('page',1),100);

        return $this->render('regions/statistiques.html.twig', [
            'formDate' => $form->createView(),
            'regions' => $regions,
            'countEnfantsRegionsYear' => $countAllMinorsForTheYear,
            'countEnfantsRegion' => $countAllMinorsOfTheRegion,
            'countAllRegionsYear' => $countAllRegionsYear,
            'countAllRegionsHommesYear' => $countAllRegionsHommesYear,
            'countAllRegionsFemmesYear' => $countAllRegionsFemmesYear,
            'countAllRegions' => $countAllRegions,
            'countAllRegionsFemmes' => $countAllRegionsFemmes,
            'countAllRegionsHommes' => $countAllRegionsHommes,
            'countAdulteRegionsYear' => $countAdulteRegionsYear,
            'countAdulteRegionsAll' => $countAdulteRegionsAll,
            'AssociationsPages' => $associationsPage
        ]);
    }

    /**
     * @Route("/{id}/edit", name="regions_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Regions $region, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(RegionsType::class, $region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('regions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('regions/edit.html.twig', [
            'region' => $region,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="regions_delete", methods={"POST"})
     */
    public function delete(Request $request, Regions $region, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isCsrfTokenValid('delete'.$region->getId(), $request->request->get('_token'))) {
            $entityManager->remove($region);
            $entityManager->flush();
        }

        return $this->redirectToRoute('regions_index', [], Response::HTTP_SEE_OTHER);
    }

    // public function exportAssociationsByRegions(ExportService $exportService, UsersRepository $usersRepository, RegionsRepository $regionsRepository)
    // {
    //     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    //     $exportService->exportAllRegions($form, $usersRepository, $regionsRepository);

    // }


    /**
     * @Route("/exportByRegions/{regionsId}/{regionsName}", name="exportByRegions", methods={"GET", "POST"})
     */
    public function ExportbyRegions(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, 
    ManagerRegistry $doctrine, ExportService $exportService, 
    RegionsRepository $regionsRepository, 
    AssociationsRepository $associationsRepository,
    $regionsId, $regionsName): Response {
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // dump($regionsId);
        // dump($regionsName);

        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $startingDate = $formData['dateDebut']->format('Y-m-d');
            $endingDate = $formData['dateFin']->format('Y-m-d');
            
            $exportService->exportAllAssocByRegions($form, $regionsRepository, $regionsId, $regionsName, $startingDate, $endingDate);     
        }

        return $this->render('statistiques/statistiques.html.twig', [
            // 'users' => $users,
            'formDate' => $form->createView(),
        ]);
    }

    
}
