<?php

namespace App\Controller;

use App\Entity\Associations;
use App\Entity\Dirigeants;
use App\Form\DirigeantsType;
use App\Repository\DirigeantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dirigeants")
 */
class DirigeantsController extends AbstractController
{
    /**
     * @Route("/", name="dirigeants_index", methods={"GET"})
     */
    public function index(DirigeantsRepository $dirigeantsRepository): Response
    {
        return $this->render('dirigeants/index.html.twig', [
            'dirigeants' => $dirigeantsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="dirigeants_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager,Associations $associations): Response
    {
        $dirigeant = new Dirigeants();
        $associations = new Associations();
        $associations->setIsDiriged(1);
        $form = $this->createForm(DirigeantsType::class, $dirigeant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dirigeant);
            $entityManager->flush();

            return $this->redirectToRoute('dirigeants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dirigeants/new.html.twig', [
            'dirigeant' => $dirigeant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="dirigeants_show", methods={"GET"})
     */
    public function show(Dirigeants $dirigeant): Response
    {
        return $this->render('dirigeants/show.html.twig', [
            'dirigeant' => $dirigeant,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="dirigeants_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Dirigeants $dirigeant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DirigeantsType::class, $dirigeant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dirigeants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dirigeants/edit.html.twig', [
            'dirigeant' => $dirigeant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="dirigeants_delete", methods={"POST"})
     */
    public function delete(Request $request, Dirigeants $dirigeant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dirigeant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dirigeant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dirigeants_index', [], Response::HTTP_SEE_OTHER);
    }
}
