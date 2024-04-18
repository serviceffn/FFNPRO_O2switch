<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Associations;
use App\Entity\Images;
use App\Form\AssociationsType;
use App\Form\DirigeantsType;
use App\Form\modifyUserType;
use App\Form\ResetPasswordType;
use App\Form\SearchAssociationsType;
use App\Form\FilterTypeAssociation;
use App\Repository\AssociationsRepository;
use App\Repository\ImagesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\ExportService;
use Doctrine\Common\Collections\ArrayCollection;




/**
 * @Route("/associations")
 */
class AssociationsController extends AbstractController
{
    /**
     * @Route("/", name="associations_index", methods={"GET", "POST"})
     */
    public function index(AssociationsRepository $associationsRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $associations = $associationsRepository->findAllAlphabetic();
        $form = $this->createForm(SearchAssociationsType::class);
        $filter = $this->createForm(FilterTypeAssociation::class);
        $search = $form->handleRequest($request);
        $searchh = $filter->handleRequest($request);
        $countInactif = $associationsRepository->countInactifAssoc();
        if ($form->isSubmitted() && $form->isValid()) {
            // On recherche les annonces correspondant aux mots clés
            $postForm = $form->getData();
            $result = $postForm['mots'];

            $allUsers = $associationsRepository->searchForm($result);
            $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);


            return $this->render('associations/index.html.twig', [

                'associations' => $associations,
                'form' => $form->createView(),
                'associationsPage' => $associationsPage,
                'filtre' => $filter->createView(),
                'countInactif' => $countInactif,
            ]);

        }

        if ($filter->isSubmitted() && $filter->isValid()) {

            $post = $filter->getData();

            if ($post['choice'] == 'Region') {
                // On recherche les annonces correspondant aux mots clés
                $allUsers = $associationsRepository->filterTypeRegion();
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

            } elseif ($post['choice'] == 'Association') {

                $allUsers = $associationsRepository->filterTypeAssociation();
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

            } elseif ($post['choice'] == 'Centre') {

                $allUsers = $associationsRepository->filterTypeCentre();
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

            }
            return $this->render('associations/index.html.twig', [

                'associations' => $associations,
                'form' => $form->createView(),
                'associationsPage' => $associationsPage,
                'filtre' => $filter->createView(),
                'countInactif' => $countInactif,
            ]);


        }


        $donnees = $associationsRepository->findAllAlphabetic();
        $associationsPage = $paginator->paginate($donnees, $request->query->getInt('page', 1), 500);





        return $this->render('associations/index.html.twig', [
            'associations' => $associations,
            'form' => $form->createView(),
            'associationsPage' => $associationsPage,
            'filtre' => $filter->createView(),
            'countInactif' => $countInactif,
        ]);
    }

    /**
     * Vérifie si une image avec le nom de fichier spécifié existe déjà dans la collection d'images.
     *
     * @param string $filename Le nom de fichier à rechercher.
     *
     * @return bool true si une image avec le nom de fichier existe déjà, false sinon.
     */
    public function hasImage(string $filename): bool
    {
        foreach ($this->images as $image) {
            if ($image->getName() === $filename) {
                return true;
            }
        }

        return false;
    }

    /**
     * @Route("/dirigeant/{id}", name="dirigeant_edit", methods={"GET","POST"})
     */
    public function diregeantShow(Request $request, Associations $association, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getId() == $id || $this->getUser()->getId() == 94) {
            $form = $this->createForm(DirigeantsType::class, $association);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                $this->getDoctrine()->getManager()->flush();

                return $this->render('associations/successEditCenter.html.twig', [
                    'association' => $association
                ]);

            }


            return $this->render('dirigeants/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }

    /**
     * @Route("/dirigeant/", name="dirigeant_index", methods={"GET"})
     */
    public function diregeantShoww(AssociationsRepository $associationsRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $dirigeants = $associationsRepository->findAllAlphabetic();

        return $this->render('dirigeants/index.html.twig', [
            'dirigeants' => $dirigeants,
        ]);
    }

    /**
     * @Route("/impression", name="impression_associations", methods={"GET"})
     */
    public function impressionAssoc(AssociationsRepository $associationsRepository, Request $request, ManagerRegistry $doctrine)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $associationsData = $doctrine->getRepository(Users::class)->findImpressionAssoc();

        $associations = [];
        $totalCount = 0;

        foreach ($associationsData as $data) {
            $associationName = $data['nom'];
            $totalLicences = $data['totalLicences'];
            $idAssoc = $data['idAssoc'];

            $totalCount += $totalLicences;

            $associations[] = [
                'name' => $associationName,
                'totalLicences' => $totalLicences,
                'idAssoc' => $idAssoc,
            ];
        }

        return $this->render('associations/impression.html.twig', [
            'associations' => $associations,
            'count' => $totalCount,
        ]);
    }


/**
 * @Route("/new", name="associations_new", methods={"GET", "POST"})
 */
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $association = new Associations();
    $association->setRoles(["ROLE_USER"]);
    $form = $this->createForm(AssociationsType::class, $association);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $post = $form->getData();
        $id = $post->getId();

        // On récupère les images transmises
        $images = $form->get('images')->getData();

        // On boucle sur les images
        foreach ($images as $image) {
            // On génère un nouveau nom de fichier
            $fichier = $id . 'png';

            // On copie le fichier dans le dossier uploads
            $image->move(
                $this->getParameter('images_directory'),
                $fichier
            );

            // On stocke l'image dans la base de données (son nom)
            $img = new Images();
            $img->setName($fichier);
            $association->addImage($img);
        }

        $entityManager->persist($association);
        $entityManager->flush();

        return $this->render('associations/onsuccess.html.twig', [
            'association' => $association,
        ]);
    }

    return $this->render('associations/new.html.twig', [
        'association' => $association,
        'form' => $form->createView(),
    ]);

}


    /**
     * @Route("/inactif", name="associations_inactif", methods={"GET"})
     */
    public function showInactifAssoc(Request $request, AssociationsRepository $associationsRepository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $associations = $associationsRepository->findInactifAssoc();
        $associationsPage = $paginator->paginate($associations, $request->query->getInt('page', 1), 500);

        return $this->render('associations/showInactif.html.twig', [
            'associationsPage' => $associationsPage,
        ]);
    }

    /**
     * @Route("/csv", name="csv_associations")
     */
    public function downloadCSV(AssociationsRepository $associationsRepository, ExportService $exportService)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $exportAssocRepository = $exportService->exportAssocRepository($associationsRepository);
    }

    /**
     * @Route("/{id}", name="associations_show", methods={"GET"})
     */
    public function show(Request $request, Associations $association): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(AssociationsType::class, $association);
        $form->handleRequest($request);

        return $this->render('associations/show.html.twig', [
            'association' => $association,
        ]);
    }


    /**
     * @Route("/my/{id}", name="associations_view", methods={"GET"})
     */
    public function showMyCentre(Request $request, Associations $association): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->getUser()->getId() == $association->getId()) {
            $form = $this->createForm(AssociationsType::class, $association);
            $form->handleRequest($request);

            return $this->render('associations/show.html.twig', [
                'association' => $association,
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }


    /**
     * @Route("/editAdmin/passwordAdmin/{associationId}", name="editPassAdmin", methods={"GET","POST"})
     */
    public function editPasswordAdmin(Request $request, UserPasswordEncoderInterface $encoder, $associationId)
    {
        $defaultHashedPassword = '$argon2id$v=19$m=65536,t=4,p=1$F8Tz+eq2QLpst8/4osFqaw$TwwUiuDkqf4PYpcEBFn+QbwnTnvR2NGxPQ8FHagDAvI';
        
        $association = $this->getDoctrine()->getRepository(Associations::class)->find($associationId);
        
        if (!$association) {
            throw $this->createNotFoundException('Association non trouvée');
        }
        
        $user = $this->getUser();
    
        if ($request->query->get('restoreDefaultPassword')) {
            $association->setPassword($defaultHashedPassword);
            $this->getDoctrine()->getManager()->flush();
    
            echo "<p style='text-align:center;color:green;font-weight:bold'>Le mot de passe par défaut a été rétabli avec succès</p>";
        }
    
        return $this->render('associations/resetPassword.html.twig', [
            'association' => $association,
        ]);
    }

    /**
     * @Route("/edit/password/", name="editPass", methods={"GET","POST"})
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $encoder)
    {

        $user = $this->getUser();

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $passwordEncoded = $encoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($passwordEncoded);
            $this->getDoctrine()->getManager()->flush();


            $this->addFlash(
                'success',
                "Votre mot de passe a bien été modifié<br>Vous devez vous reconnecter !"
            );

            return $this->redirectToRoute('app_logout');

            //return $this->redirectToRoute('app_logout');


        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
            // 'association' => $association,
        ]);
    }



    /**
     * @Route("/success/edit", name="associations_success", methods={"GET","POST"})
     */
    public function editSuccess(Request $request, Associations $association): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');


        return $this->render('associations/successEditCenter.html.twig', [
            'association' => $association
        ]);
    }


    /**
     * @Route("/{id}/edit", name="associations_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Associations $association, $id, ImagesRepository $imageRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getId() == $association->getId() || $this->getUser()->getId() == 94) {
            $user = $this->getUser();
            $nom = $user->getNom();

            $form = $this->createForm(modifyUserType::class, $association);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {



                // On récupère les images transmises
                $images = $form->get('images')->getData();

                // On boucle sur les images
                foreach ($images as $image) {


                    $fichier = $id . '.png';


                    // On copie le fichier dans le dossier uploads


                    $existingImage = $imageRepository->findOneBy(['name' => $fichier]);

                    if (!$existingImage) {
                        // On stocke l'image dans la base de données (son nom)
                        $img = new Images();
                        $img->setName($fichier);
                        $association->addImage($img);

                        // echo "<p style='text-color:red'>Veuillez dans un premier temps, cliquer sur supprimer à coté de votre ancien logo.</p>";
                    } else {
                        echo "<p style='color:red; font-weight: bold; font-size: 200%;'>Veuillez dans un premier temps, cliquer sur supprimer à coté de votre ancien logo.</p>";
                        return $this->render('associations/edit.html.twig', [
                            'association' => $association,
                            'form' => $form->createView(),
                        ]);
                    }

                    $image->move(
                        $this->getParameter('images_directory'),
                        $fichier
                    );
                }


                $this->getDoctrine()->getManager()->flush();

                if ($nom == "Fédération française de Naturisme") {

                    return $this->redirectToRoute('associations_index');

                } else {

                    return $this->render('associations/successEditCenter.html.twig', [
                        'association' => $association,
                    ]);
                }
            }

            return $this->render('associations/edit.html.twig', [
                'association' => $association,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }



    /**
     * @Route("/{id}/desactivate", name="associations_desactivate", methods={"GET", "POST"})
     */
    public function desactivate(Associations $association, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $association->setIsActive('0');
        $association->setRoles(["ROLE_INACTIF"]);

        $this->getDoctrine()->getManager()->flush();


        return $this->render('associations/show.html.twig', [
            'association' => $association,
        ]);
    }

    /**
     * @Route("/{id}/activate", name="associations_activate", methods={"GET", "POST"})
     */
    public function activate(Associations $association, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $association->setIsActive('1');
        $association->setRoles(["ROLE_USER"]);
        $this->getDoctrine()->getManager()->flush();


        return $this->render('associations/show.html.twig', [
            'association' => $association,
        ]);
    }

    /**
     * @Route("/{id}", name="associations_delete", methods={"POST"})
     */
    public function delete(Request $request, Associations $association, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isCsrfTokenValid('delete' . $association->getId(), $request->request->get('_token'))) {
            $entityManager->remove($association);
            $entityManager->flush();
        }

        return $this->redirectToRoute('associations_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/supprime/image/{id}", name="associations_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Images $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // On récupère le nom de l'image
            $nom = $image->getName();
            // On supprime le fichier
            unlink($this->getParameter('images_directory') . '/' . $nom);

            // On supprime l'entrée de la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }


}
