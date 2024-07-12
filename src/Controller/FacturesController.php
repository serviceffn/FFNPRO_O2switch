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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
    public function deposerFacture(Request $request, Associations $association, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $facture = new Facture();
        $facture->setAssociationId($association->getId());
        $facture->setCreatedAt(new \DateTime());
        $facture->setUpdatedAt(new \DateTime());

        $form = $this->createForm(FactureType::class, $facture, [
            'is_deposer_action' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pdfFile = $form->get('pdfContent')->getData();
            if ($pdfFile) {
                try {
                    $pdfContent = file_get_contents($pdfFile->getPathname());
                    $facture->setPdfContent($pdfContent);
                    $facture->setPdfFilename($form->get('pdfFilename')->getData()); // Enregistrer le nom du fichier PDF
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du traitement du fichier PDF.');
                    return $this->redirectToRoute('deposer_facture', ['id' => $association->getId()]);
                }
            }

            $entityManager->persist($facture);
            $entityManager->flush();

            $this->sendEmailNotification($mailer, $association, $facture);

            $this->addFlash('success', 'La facture a été déposée avec succès.');
            return $this->redirectToRoute('factures_index');
        }

        return $this->render('facturation/deposer_facture.html.twig', [
            'form' => $form->createView(),
            'association' => $association,
        ]);
    }

    private function sendEmailNotification(MailerInterface $mailer, Associations $association, Facture $facture)
    {
        $email = (new Email())
            ->from('no.reply.naturisme@gmail.com')
            ->to($association->getEmailPresident())
            ->subject('Nouvelle facture FFN')
            ->html(sprintf('Une nouvelle facture a été déposée dans votre espace FFN PRO %s.', $association->getNom()));

        $mailer->send($email);
    }


    /**
     * @Route("/factures/show/{associationId}", name="show_list_factures")
     */
    public function showAllFactures(EntityManagerInterface $entityManager, int $associationId): Response
    {
        $factures = $entityManager->getRepository(Facture::class)->findBy(['associationId' => $associationId]);

        return $this->render('facturation/show_list_factures.html.twig', [
            'factures' => $factures,
            'associationId' => $associationId
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
        return $this->redirectToRoute('show_list_factures', ['associationId' => $facture->getAssociationId()]);
    }

    /**
     * @Route("/factures/edit/{id}", name="edit_facture")
     */
    public function editFacture(Request $request, Facture $facture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $facture->setUpdatedAt(new \DateTime());

            $entityManager->persist($facture);
            $entityManager->flush();

            $this->addFlash('success', 'Le nom de la facture a été modifié avec succès.');
            return $this->redirectToRoute('show_list_factures', ['associationId' => $facture->getAssociationId()]);
        }

        return $this->render('facturation/edit_facture.html.twig', [
            'form' => $form->createView(),
            'facture' => $facture,
        ]);
    }

    /**
     * @Route("/factures/delete/{id}", name="delete_facture", methods={"DELETE"})
     */
    public function deleteFacture(Request $request, Facture $facture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $facture->getId(), $request->headers->get('X-CSRF-TOKEN'))) {
            $entityManager->remove($facture);
            $entityManager->flush();
            $this->addFlash('success', 'La facture a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'La validation CSRF a échoué.');
        }

        return $this->redirectToRoute('show_list_factures', ['associationId' => $facture->getAssociationId()]);
    }

    /**
     * @Route("/factures/association/{id}", name="factures_association")
     */
    public function facturesAssociation(Associations $association, EntityManagerInterface $entityManager): Response
    {
        $factures = $entityManager->getRepository(Facture::class)->findBy(['associationId' => $association->getId()]);

        return $this->render('facturation/factures_association.html.twig', [
            'factures' => $factures,
            'association' => $association,
        ]);
    }

}
