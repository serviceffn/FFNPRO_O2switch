<?php

namespace App\Controller;

use App\Repository\PrixRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Prix;
use Doctrine\ORM\EntityManagerInterface;

class ModificationController extends AbstractController
{
    /**
     * @Route("/modification", name="modification", methods={"GET", "POST"})
     */
    public function modification(EntityManagerInterface $entityManager, Request $request)
    {
        // Récupérer les prix actuels depuis la base de données
        $prix = $entityManager->getRepository(Prix::class)->findAll();

        if ($request->isMethod('POST')) {
            // Traitement du formulaire pour chaque type de licence
            foreach ($prix as $p) {
                $newPrice = $request->request->get('prix_' . $p->getId());
                if ($newPrice !== null) {
                    $p->setPrix(floatval($newPrice));
                }
            }

            // Sauvegarde des modifications
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Les prix ont été mis à jour avec succès.');
            return $this->redirectToRoute('modification');
        }

        return $this->render('modification/modification.html.twig', [
            'prix' => $prix,
        ]);
    }


    public function showPricesInscription(EntityManagerInterface $entityManager)
    {
        $prixCentre = $entityManager->getRepository(Prix::class)->findPriceByTypeLicence('Centre');
        $prixAssociation = $entityManager->getRepository(Prix::class)->findPriceByTypeLicence('Association');
    
        $prix = $entityManager->getRepository(Prix::class)->findAll();

        
        dump($prixCentre, $prixAssociation);
        
        if (!$prixCentre) {
            throw $this->createNotFoundException('Prix pour les centres non trouvé.');
        }
        if (!$prixAssociation) {
            throw $this->createNotFoundException('Prix pour les associations non trouvé.');
        }
    
        return $this->render('users/onsuccess.html.twig', [
            'prixCentre' => $prixCentre,
            'prixAssociation' => $prixAssociation,
        ]);
    }
    
    
    public function showPricesRenouvellement(EntityManagerInterface $entityManager)
    {
        $prixCentre = $entityManager->getRepository(Prix::class)->findPriceByTypeLicence('Centre');
        $prixAssociation = $entityManager->getRepository(Prix::class)->findPriceByTypeLicence('Association');
    
        dump($prixCentre, $prixAssociation);

        $prix = $entityManager->getRepository(Prix::class)->findAll();

        
        if (!$prixCentre) {
            throw $this->createNotFoundException('Prix pour les centres non trouvé.');
        }
        if (!$prixAssociation) {
            throw $this->createNotFoundException('Prix pour les associations non trouvé.');
        }
    
        return $this->render('users/renouvellement.html.twig', [
            'prixCentre' => $prixCentre,
            'prixAssociation' => $prixAssociation,
        ]);
    }
    
}
