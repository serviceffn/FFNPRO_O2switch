<?php

namespace App\Controller;

use App\Repository\PrixRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Prix;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


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
            foreach ($prix as $p) {
                $newPrice = $request->request->get('prix_' . $p->getId());
                if ($newPrice !== null) {
                    $p->setPrix(floatval($newPrice));
                }
            }

            // Gestion de l'upload de l'image
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $uploadDirectory = $this->getParameter('images_directory');
                $existingImagePath = $uploadDirectory . '/inf.gif';

                try {
                    if (file_exists($existingImagePath)) {
                        unlink($existingImagePath);
                    }

                    $imageFile->move($uploadDirectory, 'inf.gif');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont été appliqués avec succès.');
            return $this->redirectToRoute('modification');
        }

        return $this->render('modification/modification.html.twig', [
            'prix' => $prix,
        ]);
    }  
}
