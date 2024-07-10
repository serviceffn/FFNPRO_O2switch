<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Associations;
use App\Entity\Facture;
use App\Form\FactureType;
use Doctrine\ORM\EntityManagerInterface;

class FacturesController extends AbstractController
{
    /**
     * @Route("/factures", name="factures_index")
     */
    public function index(): Response
    {
        // Récupérer la liste des associations depuis la base de données
        $associations = $this->getDoctrine()->getRepository(Associations::class)->findAll();

        return $this->render('facturation/index.html.twig', [
            'associations' => $associations,
        ]);
    }

    /**
     * @Route("/factures/deposer/{id}", name="deposer_facture")
     */
    public function deposerFacture(Request $request, Associations $association, EntityManagerInterface $entityManager): Response
    {
        $facture = new Facture();
        $facture->setAssociationId($association->getId());
        $facture->setCreatedAt(new \DateTime());
        $facture->setUpdatedAt(new \DateTime());


        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pdfFile = $form->get('pdfContent')->getData();
            if ($pdfFile) {
                try {
                    $pdfContent = file_get_contents($pdfFile->getPathname());
                    $facture->setPdfContent($pdfContent);
                } catch (\Exception $e) {
                    // Gérer l'exception si la lecture du fichier échoue
                    $this->addFlash('error', 'Une erreur est survenue lors du traitement du fichier PDF.');
                    return $this->redirectToRoute('deposer_facture', ['id' => $association->getId()]);
                }
            }

            $entityManager->persist($facture);
            $entityManager->flush();

            $this->addFlash('success', 'La facture a été déposée avec succès.');
            return $this->redirectToRoute('factures_index');
        }

        return $this->render('facturation/deposer_facture.html.twig', [
            'form' => $form->createView(),
            'association' => $association,
        ]);
    }

    /**
     * @Route("/factures/show", name="show_list_factures")
     */
    public function showAllFactures(EntityManagerInterface $entityManager): Response
    {
        $factures = $entityManager->getRepository(Facture::class)->findAll();

        return $this->render('facturation/show_list_factures.html.twig', [
            'factures' => $factures,
        ]);
    }

     /**
     * @Route("/factures/download/{id}", name="download_pdf")
     */
    public function downloadPdf(Facture $facture): Response
    {
        if ($facture->getPdfContent()) {
            $pdfContent = stream_get_contents($facture->getPdfContent());
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture_' . $facture->getId() . '.pdf"',
            ]);
        }

        $this->addFlash('error', 'Le fichier PDF n\'existe pas.');
        return $this->redirectToRoute('show_list_factures');
    }

}
