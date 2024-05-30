<?php

namespace App\Controller;

use App\Entity\Associations;
use App\Entity\Users;
use App\Entity\UsersFromAllYears;
use App\Entity\Historique;
use App\Form\ContactType;
use App\Form\ExportType;
use App\Form\SearchUsersType;
use App\Form\UsersType;
use App\Form\DematerialisationType;
use App\Form\UsersEditType;
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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Services\ExportService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use DateTimeImmutable;
use Symfony\Component\Filesystem\Filesystem;



/**
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", name="users_index", methods={"GET","POST"})
     */
    public function index(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, ExportService $exportService): Response
    {
        $user = new Users;
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // $user = $this->getUser();
        $users = $usersRepository->findLicenceByDESC();
        $formDate = $this->createForm(ExportType::class);
        $export = $formDate->handleRequest($request);
        $form = $this->createForm(SearchUsersType::class);
        $search = $form->handleRequest($request);
        $filtreAssoc = $this->createForm(SearchByAssociations::class);
        $formFiltre = $filtreAssoc->handleRequest($request);
        $formDematerialisation = $this->createForm(DematerialisationType::class, $user);
        $formDematerialisation->handleRequest($request);
        $id = $formFiltre->get('nom')->getData();


        if ($formFiltre->isSubmitted() && $formFiltre->isValid()) {
            // On recherche les annonces correspondant aux mots clés
            $donnees = $usersRepository->findLicenceByCentre($id);
            $associationsPage = $paginator->paginate($donnees, $request->query->getInt('page', 1), 10000);

            return $this->render('users/index.html.twig', [
                'users' => $users,
                'form' => $form->createView(),
                'formDate' => $formDate->createView(),
                'formFiltre' => $formFiltre->createview(),
                'associationsPage' => $associationsPage,
                'formDematerialisation' => $formDematerialisation->createView(),
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $post = $form->getData();

            if ($post['choice'] == 'nomprenom') {
                // On recherche les annonces correspondant aux mots clés
                $allUsers = $usersRepository->searchNomAll($search->get('nom')->getData());
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

            } elseif ($post['choice'] == 'licence') {

                $allUsers = $usersRepository->searchAll($search->get('nom')->getData());
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

            } elseif ($post['choice'] == 'nom') {

                $allUsers = $usersRepository->searchNomAllFFN($search->get('nom')->getData());
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

            } elseif ($post['choice'] == 'nomprenomanniversaire') {

                $allUsers = $usersRepository->searchNomAnnivAllFFN($search->get('nom')->getData());
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

            }
            return $this->render('users/index.html.twig', [
                'users' => $users,
                'form' => $form->createView(),
                'formDate' => $formDate->createView(),
                'formFiltre' => $formFiltre->createview(),
                'associationsPage' => $associationsPage,
                'formDematerialisation' => $formDematerialisation->createView(),
            ]);


        }



        if ($formDate->isSubmitted() && $formDate->isValid()) {

            $exportAll = $exportService->exportAll($export, $usersRepository);
            // $dateFin = $usersRepository->search($export->get('date_fin')->getData());
        }



        $donneesSecreteriat = $usersRepository->findLicenceByDESC();
        // $users = $paginator->paginate($donnees,$request->query->getInt('page',1),10);
        $associationsPage = $paginator->paginate($donneesSecreteriat, $request->query->getInt('page', 1), 25);


        return $this->render('users/index.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
            'formDate' => $formDate->createView(),
            'formFiltre' => $formFiltre->createView(),
            'formDematerialisation' => $formDematerialisation->createView(),
            'associationsPage' => $associationsPage,
        ]);
    }


    /**
     * @Route("/test/{id}", name="users_show_test", methods={"GET"})
     */
    public function showTest(Users $user, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getNom() == $user->getCentreEmetteur() || $this->getUser()->getId() == 94) {
            return $this->render('users/show.html.twig', [
                'user' => $user,
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }

    /**
     * @Route("/centre/{id}", name="users_centre", methods={"GET","POST"})
     */
    public function getLicenceByCentre(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, $id, ExportService $exportService): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getId() == $id || $this->getUser()->getId() == 94) {
            $users = $usersRepository->findAll();
            $centre = $usersRepository->findLicenceByCentre($id);

            $form = $this->createForm(SearchUsersType::class);
            $search = $form->handleRequest($request);

            $formDateAssoc = $this->createForm(ExportType::class);
            $exportAssoc = $formDateAssoc->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $post = $form->getData();

                if ($post['choice'] == 'nomprenom') {


                    $allUsers = $usersRepository->searchNom($search->get('nom')->getData(), $id);
                    $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

                } elseif ($post['choice'] == 'licence') {

                    $allUsers = $usersRepository->search($search->get('nom')->getData(), $id);
                    $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

                } elseif ($post['choice'] == 'nom') {

                    $allUsers = $usersRepository->searchNomAllASSOC($search->get('nom')->getData(), $id);
                    $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

                } elseif ($post['choice'] == 'nomprenomanniversaire') {

                    $allUsers = $usersRepository->searchNomAnnivAll($search->get('nom')->getData(), $id);
                    $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 500);

                }

                return $this->render('users/index.html.twig', [
                    'users' => $users,
                    'form' => $form->createView(),
                    'associationsPages' => $associationsPage,
                    'formDateAssoc' => $formDateAssoc->createView(),
                    'centres' => $centre,
                ]);


            }

            if ($formDateAssoc->isSubmitted() && $formDateAssoc->isValid()) {

                $exportAll = $exportService->exportAssoc($exportAssoc, $usersRepository);
            }

            $donnees = $centre;
            $associationsPage = $paginator->paginate($donnees, $request->query->getInt('page', 1), 25);
            //  $donnees = $usersRepository->findAll();
            // $users = $paginator->paginate($donnees,$request->query->getInt('page',1),10);
            // $associationsPage = $paginator->paginate($donnees,$request->query->getInt('page',1),10);

            return $this->render('users/index.html.twig', [
                'users' => $users,
                'form' => $form->createView(),
                'formDateAssoc' => $formDateAssoc->createView(),
                'associationsPages' => $associationsPage,
                'centres' => $centre,
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }

    /**
     * @Route("/imprimed/count", name="count_imprimed", methods={"POST","GET"})
     */
    public function countImprimed(UsersRepository $usersRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $count = $usersRepository->countImprimed();

        return $this->render('base.html.twig', [
            'count' => $count,
        ]);
    }

    /**
     * @Route("/send/qrcode/{chaine}", name="users_send_qrcode", methods={"GET","POST"})
     */
    public function sendQrCode($chaine, UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, MailerInterface $mailer, QrCodeService $qrcodeService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $usersRepository->showQrTrue($chaine);

        foreach ($user as $users) {
            $qrCode = $qrcodeService->qrcode($id = $users['id']);
            $imagePath = 'qr-code/' . $users['chaine'] . '.png';
            $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
            $imageData = file_get_contents($imagePath);
            $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);

            $imagePathh = 'uploads/' . $users['idassoc'] . '.png';
            $imageTypee = pathinfo($imagePathh, PATHINFO_EXTENSION);
            $imageDataa = file_get_contents($imagePathh);
            $base64Imagee = 'data:image/' . $imageTypee . ';base64,' . base64_encode($imageDataa);

            $imagePathhh = 'uploads/inf.gif';
            $imageTypeee = pathinfo($imagePathhh, PATHINFO_EXTENSION);
            $imageDataaa = file_get_contents($imagePathhh);
            $base64Imageee = 'data:image/' . $imageTypeee . ';base64,' . base64_encode($imageDataaa);

            $imagePathhhh = 'uploads/ffn.jpg';
            $imageTypeeee = pathinfo($imagePathhhh, PATHINFO_EXTENSION);
            $imageDataaaa = file_get_contents($imagePathhhh);
            $base64Imageeee = 'data:image/' . $imageTypeeee . ';base64,' . base64_encode($imageDataaaa);

            $html = "<html><body><div style='width:90%;border-radius:20px;border:1px solid black;box-shadow: 5px 10px 18px #888888;padding:5%' ><img src='" . $base64Imagee . "' alt='Image' height='10%' style='float:right;' >" . $users['nom'] . " " . $users['prenom'] . "<div  width='100%'' style='margin-right:35%'>" . $users['n_licence'] . "<br>" . $users['nomm'] . "<br>" . $users['adresseassoc'] . "<br>" . $users['villeassoc'] . " " . $users['zipassoc'] . "<br>" . $users['emailassoc'] . "</p></div><div style='display: flex;flex-direction: row;align-items: top;justify-content: left;margin-top:10%'><img src='" . $base64Imageeee . "'  alt='Image 2' style='' width='18%' height='20%'><div class='text-container' style='width:35%;position:absolute'><p style='font-size: 10px;font-weight: bold;margin: 0 20px;'>26 rue Paul Belmondo<br>75012 Paris - 01.48.10.31.00<br>contact@ffn-naturisme.com</p><br><div style='border-bottom: 1px solid black;margin-top:3%'></div><br><p style='font-size: 10px;position:absolute;font-weight: bold;margin: 0 20px;'>Assurance MAIF 4274207 D<br></p></div><img src='" . $base64Imageee . "'  alt='Image 2' style='width:15%;width: 80px;height: 80px;margin: 0 20px;margin-left:45%'> <img src='" . $base64Image . "'  alt='Image 2' style='width: 80px;height: 80px;margin: 0 20px;'> </div></div></body></html>";

            // Convertir le HTML en 
            $options = new Options();
            $options->setIsRemoteEnabled(true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $imageData = $dompdf->output();

            // Enregistrer l'image dans le dossier public/images
            $filesystem = new Filesystem();
            $imagePath = 'images/image_' . $chaine . '.pdf';
            $filesystem->dumpFile($imagePath, $imageData);

            $destinataire = $users['email'];

            if ($destinataire != NULL) {
                $email = (new Email())
                    ->from("ffn.naturisme.no.reply@gmail.com")
                    ->to($destinataire)
                    ->subject('Fédération française de Naturisme')
                    ->embed(fopen('../public/uploads/94.png', 'r'), 'logo')
                    ->embedFromPath('../public/images/image_' . $users["chaine"] . '.pdf', 'qr')
                    ->html('<img src="cid:logo"><br><br>' . $users["nom"] . ' ' . $users["prenom"] . ',<br><br>Nous vous remercions de l\'intéret que vous portez à la Fédération.<br><br>Votre numéro de licence est : <b>' . $users["n_licence"] . '</b><br><br>Vous pouvez retrouver le guide naturiste <a href="https://ffn-naturisme.com/boutique/produit/guide-naturisme-ffn/">en cliquant ici</a><br><br>Vous pouvez retrouver votre QRCode qui sert de licence (Ne pas oublier une pièce d\'identité)<br>Si votre QRCode ne s\'affiche pas veuillez <a href="https://ffnpro.net/users/qrcode/' . $users["chaine"] . '"><b>cliquer ici</b></a><br>Vous pouvez l\'imprimer sur votre ordinateur en format paysage ou sur téléphone faire une capture d\'écran à présenter<br>Agréable journée !');
                $mailer->send($email);
            } else {
                echo 'Pas d\'adresse mail associé à ce compte';
            }


        }

        return $this->render('qrcode/successSendQrCode.html.twig', [
            'user' => $user,
        ]);
    }


    /**
     * @Route("/search", name="user_wordpress")
     */
    public function searchWordpress(Request $request, EntityManagerInterface $em)
    {
        // Récupération de la chaîne de recherche depuis la requête
        $searchTerm = $request->query->get('q');

        // Création de la requête pour récupérer les utilisateurs qui correspondent


        // Passage des utilisateurs à la vue pour affichage
        return $this->render('user/search.html.twig', [
            'searchTerm' => $searchTerm,
        ]);
    }


    /**
     * @Route("/licence/direct", name="licence_direct_all", methods={"POST","GET"})
     */
    public function licenceDirectAll(UsersRepository $usersRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $searchTerm = $request->query->get('q');
        $result = $usersRepository->findLicenceDirectAll();
        $resultt = $usersRepository->searchWordpress($searchTerm);

        $associationsPage = $paginator->paginate($result, $request->query->getInt('page', 1), 25);
        $associationsPagee = $paginator->paginate($resultt, $request->query->getInt('page', 1), 25);
        return $this->render('users/licencedirect.html.twig', [
            'associationsPage' => $associationsPage,
            'associationsPagee' => $associationsPagee,
        ]);

    }


    /**
     * @Route("/licence/direct/{entry_id}", name="licence_direct_add", methods={"POST","GET"})
     */
    public function licenceDirectAllId(UsersRepository $usersRepository, PaginatorInterface $paginator, Request $request, $entry_id, QrCodeService $qrcodeService, AssociationsRepository $associationsRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $searchTerm = $entry_id;
        $resultt = $usersRepository->searchWordpresss($searchTerm);

        $user = new Users();
        $userFromAllYears = new UsersFromAllYears();

        $now = new \DateTime();
        $date = date('d.m.y');

        // Chaîne de date
        $dateString = $resultt[0]['birthday'];

        // Créer un objet DateTimeImmutable en utilisant la chaîne de date et le format
        $dateTimeObject = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);

        // Vérifier si l'objet DateTimeImmutable a été créé avec succès
        if ($dateTimeObject !== false) {
            // Utiliser l'objet DateTimeImmutable
            // echo $dateTimeObject->format('Y-m-d H:i:s');
        } else {
            // Afficher un message d'erreur si la conversion a échoué
            echo 'La chaîne de date n\'est pas valide';
        }

        $destinataire = $resultt[0]['email'];
        $nom = $resultt[0]['nom'];
        $prenom = $resultt[0]['prenom'];
        $anniversaire = $resultt[0]['birthday'];
        $genre = $resultt[0]['genre'];
        $region = $resultt[0]['region'];
        $adresse = $resultt[0]['adresse'];
        $ville = $resultt[0]['ville'];
        $pays = $resultt[0]['pays'];
        $telephone = $resultt[0]['telephone'];
        $qrcodeorimprimer = $resultt[0]['qrcodeorimprimer'];
        $difference = $now->diff($dateTimeObject, true)->y;
        $chaine = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 100);
        $anniversaire = $dateTimeObject;

        $qrCode = $qrcodeService->qrcode($chaine);
        $blockedUser = $usersRepository->findBlockUser($prenom, $nom, $anniversaire);
        // var_dump($blockedUser);var_dump($nom);var_dump($prenom);var_dump($anniversaire);die();
        if (empty($blockedUser)) {

            $charAuthorized = "0123456789";
            $lenghtKey = 6;

            do {
                $random = substr(str_shuffle($charAuthorized), 0, $lenghtKey);
                $key = date('Y') . "-" . $random;
            } while ($usersRepository->findOneBy(['n_licence' => $key]));

            // if($post->getImpression() == 0){
            //     $user->setIsImprimed(1);
            // }else{
            //     $user->setIsImprimed(0);
            // }
            $user->setCreatedAt(new \DateTime('now'));
            $user->setNom($nom);
            $user->setPrenom($prenom);

            $userFromAllYears->setCreatedAt(new \DateTime('now'));
            $userFromAllYears->setNom($nom);
            $userFromAllYears->setPrenom($prenom);
            if ($genre == "Mr") {
                $user->setGenre("Masculin");
                $userFromAllYears->setGenre("Masculin");
            } else {
                $user->setGenre("Feminin");
                $userFromAllYears->setGenre("Feminin");
            }
            // if($qrcodeorimprimer == "Papier"){
            //     $user->setIsImprimed("0");
            //     $user->setImpression("1");
            // }else{
            //     $user->setIsImprimed("1");
            //     $user->setImpression("0");
            // }
            $user->setZip($ville);
            $user->setRenouvellementAt(new \DateTime('0000-00-00 00:00:00'));
            $user->setNLicence($key);
            $user->setIsActive(1);
            $user->setCentreEmetteur($this->getUser());
            $user->setChaine();
            $user->setAnniversaire($anniversaire);
            $user->setAdresse($adresse);
            $user->setVille($ville);
            $user->setPays($pays);
            $user->setTelephone($telephone);
            $user->setEmail($destinataire);

            $userFromAllYears->setZip($ville);
            $userFromAllYears->setRenouvellementAt(new \DateTime('0000-00-00 00:00:00'));
            $userFromAllYears->setNLicence($key);
            $userFromAllYears->setIsActive(1);
            $userFromAllYears->setCentreEmetteur($this->getUser());
            $userFromAllYears->setChaine();
            $userFromAllYears->setAnniversaire($anniversaire);
            $userFromAllYears->setAdresse($adresse);
            $userFromAllYears->setVille($ville);
            $userFromAllYears->setPays($pays);
            $userFromAllYears->setTelephone($telephone);
            $userFromAllYears->setEmail($destinataire);

            $ids = $this->getUser()->getId();
            $associations = $associationsRepository->find($ids);
            $update = $associations->setUpdatedAt(new \DateTime());

            $entityManager->persist($user);
            $entityManager->persist($update);
            $entityManager->flush();

            $entityManager->persist($userFromAllYears);
            $entityManager->persist($update);
            $entityManager->flush();

            $historique = new Historique();
            $historique->setUser($user);
            $historique->setAssociation($associations);
            $historique->setYear(date('Y'));
            $historique->setDate(new \DateTime());

            $entityManager->persist($historique);
            $entityManager->flush();

            $qrCode = $qrcodeService->qrcode($user->getChaine());
            $userss = $usersRepository->showQrTrue($user->getChaine());

            foreach ($userss as $users) {

                $qrCode = $qrcodeService->qrcode($chaine);
                $imagePath = 'qr-code/' . $users['chaine'] . '.png';
                $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
                $imageData = file_get_contents($imagePath);
                $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);

                $imagePathh = 'uploads/' . $users['idassoc'] . '.png';
                $imageTypee = pathinfo($imagePathh, PATHINFO_EXTENSION);
                $imageDataa = file_get_contents($imagePathh);
                $base64Imagee = 'data:image/' . $imageTypee . ';base64,' . base64_encode($imageDataa);

                $imagePathhh = 'uploads/inf.gif';
                $imageTypeee = pathinfo($imagePathhh, PATHINFO_EXTENSION);
                $imageDataaa = file_get_contents($imagePathhh);
                $base64Imageee = 'data:image/' . $imageTypeee . ';base64,' . base64_encode($imageDataaa);

                $imagePathhhh = 'uploads/ffn.jpg';
                $imageTypeeee = pathinfo($imagePathhhh, PATHINFO_EXTENSION);
                $imageDataaaa = file_get_contents($imagePathhhh);
                $base64Imageeee = 'data:image/' . $imageTypeeee . ';base64,' . base64_encode($imageDataaaa);

                $html = "<html><body><div style='width:90%;border-radius:20px;border:1px solid black;box-shadow: 5px 10px 18px #888888;padding:5%' ><img src='" . $base64Imagee . "' alt='Image' height='10%' style='float:right;' >" . $users['nom'] . " " . $users['prenom'] . "<div  width='100%'' style='margin-right:35%'>" . $users['n_licence'] . "<br>" . $users['nomm'] . "<br>" . $users['adresseassoc'] . "<br>" . $users['villeassoc'] . " " . $users['zipassoc'] . "<br>" . $users['emailassoc'] . "</p></div><div style='display: flex;flex-direction: row;align-items: top;justify-content: left;margin-top:10%'><img src='" . $base64Imageeee . "'  alt='Image 2' style='' width='18%' height='20%'><div class='text-container' style='width:35%;position:absolute'><p style='font-size: 10px;font-weight: bold;margin: 0 20px;'>26 rue Paul Belmondo<br>75012 Paris - 01.48.10.31.00<br>contact@ffn-naturisme.com</p><br><div style='border-bottom: 1px solid black;margin-top:3%'></div><br><p style='font-size: 10px;position:absolute;font-weight: bold;margin: 0 20px;'>Assurance MAIF 4274207 D<br></p></div><img src='" . $base64Imageee . "'  alt='Image 2' style='width:15%;width: 80px;height: 80px;margin: 0 20px;margin-left:45%'> <img src='" . $base64Image . "'  alt='Image 2' style='width: 80px;height: 80px;margin: 0 20px;'> </div></div></body></html>";

                // Convertir le HTML en 
                $options = new Options();
                $options->setIsRemoteEnabled(true);
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $imageData = $dompdf->output();

                // Enregistrer l'image dans le dossier public/images
                $filesystem = new Filesystem();
                $imagePath = 'images/image_' . $users['chaine'] . '.pdf';
                $filesystem->dumpFile($imagePath, $imageData);

                if ($destinataire != NULL) {
                    $email = (new Email())
                        ->from("ffn.naturisme.no.reply@gmail.com")
                        ->to($destinataire)
                        ->subject('Fédération française de Naturisme')
                        ->embed(fopen('../public/uploads/94.png', 'r'), 'logo')
                        ->embedFromPath('../public/images/image_' . $users["chaine"] . '.pdf', 'qr')
                        ->html('<img src="cid:logo"><br><br>' . $users["nom"] . ' ' . $users["prenom"] . ',<br><br>Nous vous remercions de l\'intéret que vous portez à la Fédération.<br><br>Votre numéro de licence est : <b>' . $users["n_licence"] . '</b><br><br>Vous pouvez retrouver le guide naturiste <a href="https://ffn-naturisme.com/boutique/produit/guide-naturisme-ffn/">en cliquant ici</a><br><br>Vous pouvez retrouver votre QRCode qui sert de licence (Ne pas oublier une pièce d\'identité)<br>Si votre QRCode ne s\'affiche pas veuillez <a href="https://ffnpro.net/users/qrcode/' . $users["chaine"] . '"><b>cliquer ici</b></a><br>Vous pouvez l\'imprimer sur votre ordinateur en format paysage ou sur téléphone faire une capture d\'écran à présenter<br>Agréable journée !');
                    $mailer->send($email);
                }

                return $this->render('users/onsuccess.html.twig', [
                    'user' => $user,
                    'age' => $difference,
                ]);
            }
        }
    }

    // /**
    //  * @Route("/send/email/{id}", name="users_send_email", methods={"GET","POST"})
    //  */
    // public function sendEmail(UsersRepository $usersRepository,Request $request, PaginatorInterface $paginator,MailerInterface $mailer,QrCodeService $qrcodeService ,$id): Response 
    // {
    //     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    //     $users = $usersRepository->find($id); 

    //     $qrCode = $qrcodeService->qrcode($id); 
    //     $destinataire = $users->getEmail();

    //     if($destinataire != NULL){ 
    //         $email = (new Email())
    //         ->from("ffn.naturisme.no.reply@gmail.com")
    //         ->to($destinataire)
    //         ->subject('Fédération française de Naturisme')
    //         ->embed(fopen('../public/uploads/94.png', 'r'), 'logo')
    //         ->embed(fopen('../public/qr-code/'.$users->getId().'.png','r'), 'qrcode')
    //         ->embedFromPath('../public/qr-code/'.$users->getId().'.png', 'qr')
    //         // ->html('<img src="cid:logo"><br><br>'.$users->getNom() .' '.$users->getPrenom().',<br><br>Nous vous remercions de l\'intéret que vous portez à la Fédération.<br><br>Votre numéro de licence est : <b>'.$users->getNLicence().'</b><br><br>Vous pouvez retrouver le guide naturiste <a href="https://ffn-naturisme.com/boutique/produit/guide-naturisme-ffn/">en cliquant ici</a><br><br>Vous pouvez retrouver votre QRCode qui sert de licence (Ne pas oublier une pièce d\'identité)<br><br><div style="width:90%;border-radius:20px;border:1px solid black;box-shadow: 5px 10px 18px #888888;padding:5%"><div width="100%" style="margin-right:35%"><br>'.$users->getNom() .' '.$users->getPrenom().'<br>'.$users->getNLicence().'<br>'.$users->nomm().'<br>'.$users->adresseassoc().'<br>'.$users->villeassoc().' '.$users->zipassoc().'<br>'.$users->emailassoc().'</div><div style="display: flex;flex-direction: column;align-items: center;justify-content: center;margin-top:10%"><img src="{{ asset('/uploads/ffn.jpg') }}" alt="Image 2" style="width: 100%;max-width: 200px;height: auto;margin: 0 20px;"><div class="text-container" style="width:100%;text-align: center;"><p style="font-size: 20px;font-weight: bold;margin: 0 20px;">26 rue Paul Belmondo<br>75012 Paris - 01.48.10.31.00<br>contact@ffn-naturisme.com</p><div style="border-bottom: 1px solid black;margin-top:3%"></div><p style="font-size: 20px;font-weight: bold;margin: 0 20px;">Assurance MAIF 4274207 D<br>Gestionsocietaire@maif.fr</p></div><img src={{ asset("/uploads/inf.gif") }} alt="Image 2" style="width:100%;max-width: 250px;height: auto;margin: 10px 0;"><img src={{ qrcode }} alt="Image 2" style="width: 100%;max-width: 150px;height: auto;margin: 10px 0;"></div></div><br><br>Agréable journée !');
    //         $mailer->send($email);
    //     }else{
    //         echo 'Pas d\'adresse mail associé à ce compte';
    //     }


    //     return $this->render('qrcode/successSendQrCode.html.twig', [
    //         'user' => $users,
    //     ]);
    // }

    /**
     * @Route("/impression/associations/centres/{id}", name="impression_associations_centre", methods={"GET"})
     */
    public function impressionAssoc(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $users = $usersRepository->findLicenceByDESC();
        $centre = $usersRepository->sortByAssociation($id);
        $associationsPage = $paginator->paginate($centre, $request->query->getInt('page', 1), 25);

        return $this->render('users/usersAssociations.html.twig', [
            'associationsPage' => $associationsPage,
            'users' => $users,
        ]);
    }


    /**
     * @Route("/html-to-image/{chaine}", name="html_to_image")
     */
    public function htmlToImage($chaine, UsersRepository $usersRepository, MailerInterface $mailer)
    {
        // Récupérer les utilisateurs à partir de la base de données
        $user = $usersRepository->showQrTrue($chaine);

        foreach ($user as $users) {
            $imagePath = 'qr-code/' . $users['chaine'] . '.png';
            $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
            $imageData = file_get_contents($imagePath);
            $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);

            $imagePathh = 'uploads/' . $users['idassoc'] . '.png';
            $imageTypee = pathinfo($imagePathh, PATHINFO_EXTENSION);
            $imageDataa = file_get_contents($imagePathh);
            $base64Imagee = 'data:image/' . $imageTypee . ';base64,' . base64_encode($imageDataa);

            $imagePathhh = 'uploads/inf.gif';
            $imageTypeee = pathinfo($imagePathhh, PATHINFO_EXTENSION);
            $imageDataaa = file_get_contents($imagePathhh);
            $base64Imageee = 'data:image/' . $imageTypeee . ';base64,' . base64_encode($imageDataaa);

            $imagePathhhh = 'uploads/ffn.jpg';
            $imageTypeeee = pathinfo($imagePathhhh, PATHINFO_EXTENSION);
            $imageDataaaa = file_get_contents($imagePathhhh);
            $base64Imageeee = 'data:image/' . $imageTypeeee . ';base64,' . base64_encode($imageDataaaa);

            $html = "<html><body><div style='width:90%;border-radius:20px;border:1px solid black;box-shadow: 5px 10px 18px #888888;padding:5%' ><img src='" . $base64Imagee . "' alt='Image' height='10%' style='float:right;' >" . $users['nom'] . " " . $users['prenom'] . "<div  width='100%'' style='margin-right:35%'>" . $users['n_licence'] . "<br>" . $users['nomm'] . "<br>" . $users['adresseassoc'] . "<br>" . $users['villeassoc'] . " " . $users['zipassoc'] . "<br>" . $users['emailassoc'] . "</p></div><div style='display: flex;flex-direction: row;align-items: top;justify-content: left;margin-top:10%'><img src='" . $base64Imageeee . "'  alt='Image 2' style='' width='18%' height='20%'><div class='text-container' style='width:35%;position:absolute'><p style='font-size: 10px;font-weight: bold;margin: 0 20px;'>26 rue Paul Belmondo<br>75012 Paris - 01.48.10.31.00<br>contact@ffn-naturisme.com</p><br><div style='border-bottom: 1px solid black;margin-top:3%'></div><br><p style='font-size: 10px;position:absolute;font-weight: bold;margin: 0 20px;'>Assurance MAIF 4274207 D<br></p></div><img src='" . $base64Imageee . "'  alt='Image 2' style='width:15%;width: 80px;height: 80px;margin: 0 20px;margin-left:45%'> <img src='" . $base64Image . "'  alt='Image 2' style='width: 80px;height: 80px;margin: 0 20px;'> </div></div></body></html>";

            // Convertir le HTML en 
            $options = new Options();
            $options->setIsRemoteEnabled(true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $imageData = $dompdf->output();

            // Enregistrer l'image dans le dossier public/images
            $filesystem = new Filesystem();
            $imagePath = 'images/image_' . $chaine . '.pdf';
            $filesystem->dumpFile($imagePath, $imageData);

            $destinataire = $users['email'];

            if ($destinataire != NULL) {
                $email = (new Email())
                    ->from("ffn.naturisme.no.reply@gmail.com")
                    ->to($destinataire)
                    ->subject('Fédération française de Naturisme')
                    ->embed(fopen('../public/uploads/94.png', 'r'), 'logo')
                    ->embedFromPath('../public/images/image_' . $users["chaine"] . '.pdf', 'qr')
                    ->html('<img src="cid:logo"><br><br>' . $users["nom"] . ' ' . $users["prenom"] . ',<br><br>Nous vous remercions de l\'intéret que vous portez à la Fédération.<br><br>Votre numéro de licence est : <b>' . $users["n_licence"] . '</b><br><br>Vous pouvez retrouver le guide naturiste <a href="https://ffn-naturisme.com/boutique/produit/guide-naturisme-ffn/">en cliquant ici</a><br><br>Vous pouvez retrouver votre QRCode qui sert de licence (Ne pas oublier une pièce d\'identité)<br>Si votre QRCode ne s\'affiche pas veuillez <a href="https://ffnpro.net/users/qrcode/' . $users["chaine"] . '"><b>cliquer ici</b></a><br>Vous pouvez l\'imprimer sur votre ordinateur en format paysage ou sur téléphone faire une capture d\'écran à présenter<br>Agréable journée !');
                $mailer->send($email);
            } else {
                echo 'Pas d\'adresse mail associé à ce compte';
            }
        }
    }

    /**
     * @Route("/centre/{id}/Alphabetic", name="users_centre_alphabetique", methods={"GET","POST"})
     */
    public function sortAlphabetic(UsersRepository $usersRepository, Request $request, PaginatorInterface $paginator, $id, ExportService $exportService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getId() == $id) {

            $users = $usersRepository->findAll();
            $sortAlphabetic = $usersRepository->sortAlphabetic($id);

            $form = $this->createForm(SearchUsersType::class);
            $search = $form->handleRequest($request);

            $formDateAssoc = $this->createForm(ExportType::class);
            $exportAssoc = $formDateAssoc->handleRequest($request);

            if ($formDateAssoc->isSubmitted() && $formDateAssoc->isValid()) {

                $exportAll = $exportService->exportAssoc($exportAssoc, $usersRepository);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                // On recherche les annonces correspondant aux mots clés
                // On recherche les annonces correspondant aux mots clés
                $allUsers = $usersRepository->searchCentre($search->get('nom')->getData(), $id);
                $associationsPage = $paginator->paginate($allUsers, $request->query->getInt('page', 1), 50);

                return $this->render('users/index.html.twig', [
                    'users' => $users,
                    'form' => $form->createView(),
                    'associationsPages' => $associationsPage,
                    'centres' => $sortAlphabetic,
                ]);


            }
            $donnees = $sortAlphabetic;
            $associationsPage = $paginator->paginate($donnees, $request->query->getInt('page', 1), 10000000);
            //  $donnees = $usersRepository->findAll();
            // $users = $paginator->paginate($donnees,$request->query->getInt('page',1),10);
            // $associationsPage = $paginator->paginate($donnees,$request->query->getInt('page',1),10);

            return $this->render('users/index.html.twig', [
                'users' => $users,
                'form' => $form->createView(),
                'associationsPages' => $associationsPage,
                'centres' => $sortAlphabetic,
                'formDateAssoc' => $formDateAssoc->createView(),
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }




    /**
     * @Route("/renouvellement/{id}", name="users_renouvellement", methods={"GET", "POST"})
     */
    public function getRenouvellementLicence(UsersRepository $usersRepository, AssociationsRepository $associationsRepository, Request $request, Users $user, EntityManagerInterface $entityManager, $id, MailerInterface $mailer, QrCodeService $qrcodeService): Response
    {

        $usersFromAllYears = new UsersFromAllYears();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);


        $licence_sans_annee = $usersRepository->findByLicence($id);
        $licence = $usersRepository->findByLicenceAll($id);
        $licence_new = date('Y') . $licence_sans_annee;
        $now = new \DateTime();

        // $charAuthorized = "0123456789";
        // $lenghtKey = 6;
        // $charAuthorized = str_shuffle($charAuthorized);
        // $random = substr($charAuthorized, 0, $lenghtKey);
        // $key = date('Y')."-".$random;

        // CALCUL AGE
        $post = $form->getData();
        $datetime2 = $post->getAnniversaire();
        $difference = $now->diff($datetime2, true)->y;
        $lenghtKeyError = strlen($licence);
        $qrCode = $qrcodeService->qrcode($id = $post->getId());

        // $age = $post->getAnniversaire('Y');
        // $difference = $now - $age;

        $currentYear = $now->format('Y');

        $disableDateCheck = $this->getParameter('disable_date_check');
        if (!$disableDateCheck) {
            $debutNovembre = new \DateTime($currentYear . '-11-01');
            $finDecembre = new \DateTime(($currentYear + 1) . '-01-01');
            if ($now >= $debutNovembre && $now < $finDecembre) {
                echo '<p style="color:red">Cette fonction est désactivée entre le 1er novembre et le 1er janvier</p>';
                return $this->render('users/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }
        }

        if ($licence_new != $licence) {
            $ids = $this->getUser()->getId();
            $associations = $associationsRepository->find($ids);
            $update = $associations->setUpdatedAt(new \DateTime());

            $user->setNLicence($licence_new);
            $user->setRenouvellementAt(new \DateTime());
            $user->setIsImprimed(false);
            
            $usersFromAllYears->setNLicence($licence_new);
            $usersFromAllYears->setRenouvellementAt(new \DateTime());
            $usersFromAllYears->setIsImprimed(false);

            $entityManager->flush();

            $historique = new Historique();
            $historique->setUser($user);
            $historique->setAssociation($associations);
            $historique->setYear(date('Y'));
            $historique->setDate(new \DateTime());

            $entityManager->persist($historique);
            $entityManager->flush();

            $entityManager->persist($usersFromAllYears);
            $entityManager->flush();

            // RECUPERER DONNEES POST
            $post = $form->getData();
            $contenu = "FFN - Merci de vous être licencié, voici votre numéro de licence : " . $post->getNLicence() . "";
            $destinataire = $post->getEmail();
            $licence = $post->getNlicence();
            $nom = $post->getNom();
            $prenom = $post->getPrenom();
            $chaine = $user->getChaine();
            $qrCode = $qrcodeService->qrcode($chaine);







            $user = $usersRepository->showQrTrue($chaine);

            foreach ($user as $users) {
                $qrCode = $qrcodeService->qrcode($chaine);
                $imagePath = 'qr-code/' . $users['chaine'] . '.png';
                $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
                $imageData = file_get_contents($imagePath);
                $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);

                $imagePathh = 'uploads/' . $users['idassoc'] . '.png';
                $imageTypee = pathinfo($imagePathh, PATHINFO_EXTENSION);
                $imageDataa = file_get_contents($imagePathh);
                $base64Imagee = 'data:image/' . $imageTypee . ';base64,' . base64_encode($imageDataa);

                $imagePathhh = 'uploads/inf.gif';
                $imageTypeee = pathinfo($imagePathhh, PATHINFO_EXTENSION);
                $imageDataaa = file_get_contents($imagePathhh);
                $base64Imageee = 'data:image/' . $imageTypeee . ';base64,' . base64_encode($imageDataaa);

                $imagePathhhh = 'uploads/ffn.jpg';
                $imageTypeeee = pathinfo($imagePathhhh, PATHINFO_EXTENSION);
                $imageDataaaa = file_get_contents($imagePathhhh);
                $base64Imageeee = 'data:image/' . $imageTypeeee . ';base64,' . base64_encode($imageDataaaa);

                $html = "<html><body><div style='width:90%;border-radius:20px;border:1px solid black;box-shadow: 5px 10px 18px #888888;padding:5%' ><img src='" . $base64Imagee . "' alt='Image' height='10%' style='float:right;' >" . $users['nom'] . " " . $users['prenom'] . "<div  width='100%'' style='margin-right:35%'>" . $users['n_licence'] . "<br>" . $users['nomm'] . "<br>" . $users['adresseassoc'] . "<br>" . $users['villeassoc'] . " " . $users['zipassoc'] . "<br>" . $users['emailassoc'] . "</p></div><div style='display: flex;flex-direction: row;align-items: top;justify-content: left;margin-top:10%'><img src='" . $base64Imageeee . "'  alt='Image 2' style='' width='18%' height='20%'><div class='text-container' style='width:35%;position:absolute'><p style='font-size: 10px;font-weight: bold;margin: 0 20px;'>26 rue Paul Belmondo<br>75012 Paris - 01.48.10.31.00<br>contact@ffn-naturisme.com</p><br><div style='border-bottom: 1px solid black;margin-top:3%'></div><br><p style='font-size: 10px;position:absolute;font-weight: bold;margin: 0 20px;'>Assurance MAIF 4274207 D<br></p></div><img src='" . $base64Imageee . "'  alt='Image 2' style='width:15%;width: 80px;height: 80px;margin: 0 20px;margin-left:45%'> <img src='" . $base64Image . "'  alt='Image 2' style='width: 80px;height: 80px;margin: 0 20px;'> </div></div></body></html>";

                // Convertir le HTML en 
                $options = new Options();
                $options->setIsRemoteEnabled(true);
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $imageData = $dompdf->output();

                // Enregistrer l'image dans le dossier public/images
                $filesystem = new Filesystem();
                $imagePath = 'images/image_' . $chaine . '.pdf';
                $filesystem->dumpFile($imagePath, $imageData);

                $destinataire = $users['email'];

                if ($destinataire != NULL) {
                    $email = (new Email())
                        ->from("ffn.naturisme.no.reply@gmail.com")
                        ->to($destinataire)
                        ->subject('Fédération française de Naturisme')
                        ->embed(fopen('../public/uploads/94.png', 'r'), 'logo')
                        ->embedFromPath('../public/images/image_' . $users["chaine"] . '.pdf', 'qr')
                        ->html('<img src="cid:logo"><br><br>' . $users["nom"] . ' ' . $users["prenom"] . ',<br><br>Nous vous remercions de l\'intéret que vous portez à la Fédération.<br><br>Votre numéro de licence est : <b>' . $users["n_licence"] . '</b><br><br>Vous pouvez retrouver le guide naturiste <a href="https://ffn-naturisme.com/boutique/produit/guide-naturisme-ffn/">en cliquant ici</a><br><br>Vous pouvez retrouver votre QRCode qui sert de licence (Ne pas oublier une pièce d\'identité)<br>Si votre QRCode ne s\'affiche pas veuillez <a href="https://ffnpro.net/users/qrcode/' . $users["chaine"] . '"><b>cliquer ici</b></a><br>Vous pouvez l\'imprimer sur votre ordinateur en format paysage ou sur téléphone faire une capture d\'écran à présenter<br>Agréable journée !');
                    $mailer->send($email);
                }
                return $this->render('users/renouvellement.html.twig', [
                    'user' => $user,
                    'licence_new' => $licence_new,
                    'licence_sans_annee' => $licence,
                    'age' => $difference,
                    'lenghtKeyError' => $lenghtKeyError,
                    // 'form' => $formDematerialisation->createView(),
                ]);

            }
        } elseif ($lenghtKeyError != 11) {

            $user->setNLicence($key);
            $entityManager->flush();

            return $this->render('users/renouvellement.html.twig', [
                'user' => $user,
                'licence_new' => $licence_new,
                'licence_sans_annee' => $licence,
                'age' => $difference,
                'lenghtKeyError' => $lenghtKeyError
            ]);

        } else {

            return $this->render('users/echecRenouvellement.html.twig', [
                'user' => $user,
            ]);
        }
    }

    /**
     * @Route("/new/{id}", name="users_new", methods={"GET", "POST"})
     */

    public function new(Request $request, EntityManagerInterface $entityManager, UsersRepository $usersRepository, $id, MailerInterface $mailer, Associations $associations, QrCodeService $qrcodeService, AssociationsRepository $associationsRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = new Users();
        $userFromAllYears = new UsersFromAllYears();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);
        // dump($form->getData());

        $now = new \DateTime();

        $formContact = $this->createForm(ContactType::class);
        $formContact->handleRequest($request);

        if ($formContact->isSubmitted() && $form->isValid()) {

            $contact = $formContact->getData();

        }

        if ($form->isSubmitted() && $form->isValid() && $form->getData()->getAnniversaire() != '0000-00-00') {


            $currentDate = date('Y-m-d');
            $currentDate = date('Y-m-d', strtotime($currentDate));

            $post = $form->getData();
            $destinataire = $post->getEmail();
            $adresse = $post->getAdresse();
            $nom = $post->getNom();
            $prenom = $post->getPrenom();
            $genre = $post->getGenre();
            $ville = $post->getVille();
            $pays = $post->getPays();
            $zip = $post->getZip();
            $centre_emetteur = $post->getCentreEmetteur();
            $region = $post->getRegion();
            $telephone = $post->getTelephone();
            $agree_terms = $post->getAgreeTerms();
            $anniversaire = $post->getAnniversaire();
            $difference = $now->diff($anniversaire, true)->y;
            $chaine = $post->getChaine();

            $existingUser = $usersRepository->findOneBy(['nom' => $nom, 'prenom' => $prenom]);

            if ($existingUser) {
                $dateDifference = $existingUser->getAnniversaire()->diff($anniversaire)->m;
                if ($dateDifference < 1) {
                    $errorMessage = 'Licencié faisant déjà partie d\'un centre ou association avec une date de naissance similaire. Veuillez contacter la FFN pour le changement.';
                    return $this->render('users/new.html.twig', [
                        'user' => $existingUser,
                        'form' => $form->createView(),
                        'errorMessage' => $errorMessage,
                    ]);
                }
            }
            

            $Checking_firstname_lastname_email = $usersRepository->findBy(['nom' => $nom, 'prenom' => $prenom, 'email' => $destinataire]);

            if (!empty($Checking_firstname_lastname_email)) {
                $errorMessage = 'Licencié faisant déjà partie d\'un centre ou association. Veuillez contacter la FFN pour le changement.';
                return $this->render('users/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                    'errorMessage' => $errorMessage,
                ]);
            }

            $blockedUser = $usersRepository->findBlockUser($prenom, $nom, $anniversaire);

            $currentYear = $now->format('Y');

            $disableDateCheck = $this->getParameter('disable_date_check');
            if (!$disableDateCheck) {
                $debutNovembre = new \DateTime($currentYear . '-11-01');
                $finDecembre = new \DateTime(($currentYear + 1) . '-01-01');
                if ($now >= $debutNovembre && $now < $finDecembre) {
                    echo '<p style="color:red">Cette fonction est désactivée entre le 1er novembre et le 1er janvier</p>';
                    return $this->render('users/new.html.twig', [
                        'user' => $user,
                        'form' => $form->createView(),
                    ]);
                }
            }

            if (empty($blockedUser)) {

                $charAuthorized = "0123456789";
                $lenghtKey = 6;
                

                do {
                    $random = substr(str_shuffle($charAuthorized), 0, $lenghtKey);
                    $key = date('Y') . "-" . $random;
                } while ($usersRepository->findOneBy(['n_licence' => $key]));

                if ($post->getImpression() == 0) {
                    $user->setIsImprimed(1);
                } else {
                    $user->setIsImprimed(0);
                }

                $user->setCreatedAt(new \DateTime('now'));
                $user->setRenouvellementAt(new \DateTime());
                $user->setNLicence($key);
                $user->setIsActive(1);
                $user->setCentreEmetteur($this->getUser());
                $user->setChaine();
                $chaine = $user->getChaine();
                
                $userFromAllYears->setNom($nom);
                $userFromAllYears->setPrenom($prenom);
                $userFromAllYears->setAdresse($adresse);
                $userFromAllYears->setGenre($genre);
                $userFromAllYears->setPays($pays);
                $userFromAllYears->setZip($zip);
                $userFromAllYears->setVille($ville);
                $userFromAllYears->setEmail($destinataire);
                $userFromAllYears->setTelephone($telephone);
                $userFromAllYears->setAnniversaire($anniversaire);
                $userFromAllYears->setRegion($region);
                $userFromAllYears->setCreatedAt(new \DateTime('now'));
                $userFromAllYears->setRenouvellementAt(new \DateTime());
                $userFromAllYears->setNLicence($key);
                $userFromAllYears->setIsActive(1);
                $user->setImprimedAt(new \DateTime());
                $userFromAllYears->setCentreEmetteur($this->getUser());
                $userFromAllYears->setChaine();
                $chaine = $user->getChaine();


                $ids = $this->getUser()->getId();
                $associations = $associationsRepository->find($ids);
                $update = $associations->setUpdatedAt(new \DateTime());

                $entityManager->persist($user);
                $entityManager->persist($update);
                $entityManager->flush();

                $entityManager->persist($userFromAllYears);
                $entityManager->flush();

                $historique = new Historique();
                $historique->setUser($user);
                $historique->setAssociation($associations);
                $historique->setYear(date('Y'));
                $historique->setDate(new \DateTime());

                $entityManager->persist($historique);
                $entityManager->flush();


                $userss = $usersRepository->showQrTrue($chaine);
                foreach ($userss as $users) {
                    // dump($userss);

                    $qrCode = $qrcodeService->qrcode($users['chaine']);
                    $imagePath = 'qr-code/' . $users['chaine'] . '.png';
                    $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
                    $imageData = file_get_contents($imagePath);
                    $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);

                    $imagePathh = 'uploads/' . $users['idassoc'] . '.png';
                    $imageTypee = pathinfo($imagePathh, PATHINFO_EXTENSION);
                    $imageDataa = file_get_contents($imagePathh);
                    $base64Imagee = 'data:image/' . $imageTypee . ';base64,' . base64_encode($imageDataa);

                    $imagePathhh = 'uploads/inf.gif';
                    $imageTypeee = pathinfo($imagePathhh, PATHINFO_EXTENSION);
                    $imageDataaa = file_get_contents($imagePathhh);
                    $base64Imageee = 'data:image/' . $imageTypeee . ';base64,' . base64_encode($imageDataaa);

                    $imagePathhhh = 'uploads/ffn.jpg';
                    $imageTypeeee = pathinfo($imagePathhhh, PATHINFO_EXTENSION);
                    $imageDataaaa = file_get_contents($imagePathhhh);
                    $base64Imageeee = 'data:image/' . $imageTypeeee . ';base64,' . base64_encode($imageDataaaa);

                    $html = "<html><body><div style='width:90%;border-radius:20px;border:1px solid black;box-shadow: 5px 10px 18px #888888;padding:5%' ><img src='" . $base64Imagee . "' alt='Image' height='10%' style='float:right;' >" . $users['nom'] . " " . $users['prenom'] . "<div  width='100%'' style='margin-right:35%'>" . $users['n_licence'] . "<br>" . $users['nomm'] . "<br>" . $users['adresseassoc'] . "<br>" . $users['villeassoc'] . " " . $users['zipassoc'] . "<br>" . $users['emailassoc'] . "</p></div><div style='display: flex;flex-direction: row;align-items: top;justify-content: left;margin-top:10%'><img src='" . $base64Imageeee . "'  alt='Image 2' style='' width='18%' height='20%'><div class='text-container' style='width:35%;position:absolute'><p style='font-size: 10px;font-weight: bold;margin: 0 20px;'>26 rue Paul Belmondo<br>75012 Paris - 01.48.10.31.00<br>contact@ffn-naturisme.com</p><br><div style='border-bottom: 1px solid black;margin-top:3%'></div><br><p style='font-size: 10px;position:absolute;font-weight: bold;margin: 0 20px;'>Assurance MAIF 4274207 D<br></div><img src='" . $base64Imageee . "'  alt='Image 2' style='width:15%;width: 80px;height: 80px;margin: 0 20px;margin-left:45%'> <img src='" . $base64Image . "'  alt='Image 2' style='width: 80px;height: 80px;margin: 0 20px;'> </div></div></body></html>";

                    $options = new Options();
                    $options->setIsRemoteEnabled(true);
                    $dompdf = new Dompdf($options);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    $imageData = $dompdf->output();

                    // Enregistrer l'image dans le dossier public/images
                    $filesystem = new Filesystem();
                    $imagePath = 'images/image_' . $chaine . '.pdf';
                    $filesystem->dumpFile($imagePath, $imageData);

                    $destinataire = $users['email'];
                    if ($destinataire != NULL) {
                        $email = (new Email())
                            ->from("ffn.naturisme.no.reply@gmail.com")
                            ->to($destinataire)
                            ->subject('Fédération française de Naturisme')
                            ->embed(fopen('../public/uploads/94.png', 'r'), 'logo')
                            ->embedFromPath('../public/images/image_' . $users["chaine"] . '.pdf', 'qr')
                            ->html('<img src="cid:logo"><br><br>' . $users["nom"] . ' ' . $users["prenom"] . ',<br><br>Nous vous remercions de l\'intéret que vous portez à la Fédération.<br><br>Votre numéro de licence est : <b>' . $users["n_licence"] . '</b><br><br>Vous pouvez retrouver le guide naturiste <a href="https://ffn-naturisme.com/boutique/produit/guide-naturisme-ffn/">en cliquant ici</a><br><br>Vous pouvez retrouver votre QRCode qui sert de licence (Ne pas oublier une pièce d\'identité)<br>Si votre QRCode ne s\'affiche pas veuillez <a href="https://ffnpro.net/users/qrcode/' . $users["chaine"] . '"><b>cliquer ici</b></a><br>Vous pouvez l\'imprimer sur votre ordinateur en format paysage ou sur téléphone faire une capture d\'écran à présenter<br>Agréable journée !');
                        $mailer->send($email);
                    }
                }


                return $this->render('users/onsuccess.html.twig', [
                    'user' => $user,
                    'age' => $difference,
                ]);

            }
        } else {

            if ($form->isSubmitted()) {
                echo '<p style="color:red;font-weight:bold">Licencié faisant déjà partie d\'un centre ou association. Veuillez contacter la FFN pour le transfert d\'association.</p>';
            }
        }
        return $this->render('users/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            // 'errorMessage' => $errorMessage,
        ]);
    }


    /**
     * @Route("/{id}", name="users_show", methods={"GET"})
     */
    public function show(Users $user, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getNom() == $user->getCentreEmetteur() || $this->getUser()->getId() == 94) {
            return $this->render('users/show.html.twig', [
                'user' => $user,
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }

    /**
     * @Route("/qrcode/{chaine}", name="users_show_qrcode", methods={"GET"})
     */
    public function showQrCode(Users $user, QrcodeService $qrcodeService, $chaine, UsersRepository $usersRepository): Response
    {

        $date = date('Y');
        $qrCode = $qrcodeService->qrcode($chaine);
        $sql = $usersRepository->showQrCode($chaine);

        return $this->render('users/showqrcode.html.twig', [
            'user' => $sql,
            'date' => $date,
            'qrcode' => $qrCode,
            // 'photoCentre' => $sql
        ]);

    }



    /**
     * @Route("/renouvellement/{id}/choice", name="choice_renouvellement", methods={"GET"})
     */
    public function choiceImpression(Users $user, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('users/choixRenouvellement.html.twig', [
            'user' => $user,
        ]);

    }


    /**
     * @Route("/edit/{id}/choice", name="choice_edit", methods={"GET"})
     */
    public function choiceEditImpression(Users $user, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('users/choixRenouvellement.html.twig', [
            'user' => $user,
        ]);

    }

    /**
     * @Route("/edit/{id}/choice/qrcode", name="choice_edit_qrcode", methods={"GET"})
     */
    public function choiceEditImpressionQrcode(Users $user, $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user->setIsImprimed(true);
        $user->setImpression(false);
        $user->setImprimedAt(new \DateTime());
        $entityManager->flush();

        return $this->render('users/editSuccessChoice.html.twig', [
            'user' => $user,
            // 'form' => $formDematerialisation->createView(),
        ]);

    }

    /**
     * @Route("/edit/{id}/choice/physique", name="choice_edit_physique", methods={"GET"})
     */
    public function choiceEditImpressionPhysique(Users $user, $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user->setIsImprimed(true);
        $user->setImpression(true);
        $user->setImprimedAt(new \DateTime());
        $entityManager->flush();

        return $this->render('users/editSuccessChoice.html.twig', [
            'user' => $user,
        ]);

    }






    /**
     * @Route("/edit/{id}", name="users_edit", methods={"GET", "POST"})
     */
    public function edit(UsersRepository $usersRepository, Request $request, Users $user, EntityManagerInterface $entityManager, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getNom() == $user->getCentreEmetteur() || $this->getUser()->getId() == 94) {

            $users = $this->getUser();
            $nom = $users->getNom();

            $form = $this->createForm(UsersEditType::class, $user);
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager->flush();

                if ($nom == "Fédération française de Naturisme") {

                    return $this->render('users/editSuccess.html.twig', [
                        'users' => $user,
                    ]);

                } else {

                    return $this->render('users/editSuccess.html.twig', [
                        'users' => $user,
                    ]);
                }
            }



            return $this->render('users/edit.html.twig', [
                'user' => $users,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->render('error/404.html.twig', [
            ]);
        }
    }

    /**
     * @Route("/delete/{id}", name="users_delete", methods={"POST"})
     */
    public function delete(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('accueil', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/reset/imprimed", name="users_update_imprimed_all", methods={"POST","GET"})
     */
    public function updateIsImprimed(UsersRepository $usersRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $update = $usersRepository->updateIsImprimed();

        return $this->redirectToRoute('users_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/update/imprimed/true/{id}", name="users_update_imprimed", methods={"POST","GET"})
     */
    public function updateIsImprimedId(UsersRepository $usersRepository, $id, Users $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $update = $usersRepository->updateIsImprimedId($id);


        return $this->redirectToRoute('users_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/update/imprimed/false/{id}", name="users_update_imprimed_false", methods={"POST","GET"})
     */
    public function updateIsImprimedIdFalse(UsersRepository $usersRepository, $id, Users $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $update = $usersRepository->updateIsImprimedIdFalse($id);


        return $this->redirectToRoute('users_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/renouvellement/{id}/qrcode/", name="qrcode_only", methods={"POST","GET"})
     */
    public function qrcodeOnly(UsersRepository $usersRepository, AssociationsRepository $associationsRepository, Request $request, EntityManagerInterface $entityManager, $id, MailerInterface $mailer, QrCodeService $qrcodeService, Users $user): Response
    {
        // $update = $usersRepository->updateImpression($id);

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        $formDematerialisation = $this->createForm(DematerialisationType::class, $user);
        $formDematerialisation->handleRequest($request);


        $licence_sans_annee = $usersRepository->findByLicence($id);
        $licence = $usersRepository->findByLicenceAll($id);
        $licence_new = date('Y') . $licence_sans_annee;
        $now = new \DateTime();

        // $charAuthorized = "0123456789";
        // $lenghtKey = 6;
        // $charAuthorized = str_shuffle($charAuthorized);
        // $random = substr($charAuthorized, 0, $lenghtKey);
        // $key = date('Y')."-".$random;

        // CALCUL AGE
        $post = $form->getData();
        $datetime2 = $post->getAnniversaire();
        $difference = $now->diff($datetime2, true)->y;
        $lenghtKeyError = strlen($licence);

        // $age = $post->getAnniversaire('Y');
        // $difference = $now - $age;

        if ($licence_new != $licence) {
            $ids = $this->getUser()->getId();
            $associations = $associationsRepository->find($ids);
            $update = $associations->setUpdatedAt(new \DateTime());

            $user->setNLicence($licence_new);
            $user->setRenouvellementAt(new \DateTime());
            $user->setIsImprimed(true);
            $user->setImpression(false);
            $user->setImprimedAt(new \DateTime());

            $entityManager->flush();

            $historique = new Historique();
            $historique->setUser($user);
            $historique->setAssociation($associations);
            $historique->setYear(date('Y'));
            $historique->setDate(new \DateTime());

            $entityManager->persist($historique);
            $entityManager->flush();



            // RECUPERER DONNEES POST
            $post = $form->getData();
            $contenu = "FFN - Merci de vous être licencié, voici votre numéro de licence : " . $post->getNLicence() . "";
            $destinataire = $post->getEmail();
            $licence = $post->getNlicence();
            $nom = $post->getNom();
            $prenom = $post->getPrenom();

            $chaine = $user->getChaine();
            $qrCode = $qrcodeService->qrcode($chaine);


            $userss = $usersRepository->showQrTrue($chaine);

            foreach ($userss as $users) {
                $imagePath = 'qr-code/' . $users['chaine'] . '.png';
                $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
                $imageData = file_get_contents($imagePath);
                $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);

                $imagePathh = 'uploads/' . $users['idassoc'] . '.png';
                $imageTypee = pathinfo($imagePathh, PATHINFO_EXTENSION);
                $imageDataa = file_get_contents($imagePathh);
                $base64Imagee = 'data:image/' . $imageTypee . ';base64,' . base64_encode($imageDataa);

                $imagePathhh = 'uploads/inf.gif';
                $imageTypeee = pathinfo($imagePathhh, PATHINFO_EXTENSION);
                $imageDataaa = file_get_contents($imagePathhh);
                $base64Imageee = 'data:image/' . $imageTypeee . ';base64,' . base64_encode($imageDataaa);

                $imagePathhhh = 'uploads/ffn.jpg';
                $imageTypeeee = pathinfo($imagePathhhh, PATHINFO_EXTENSION);
                $imageDataaaa = file_get_contents($imagePathhhh);
                $base64Imageeee = 'data:image/' . $imageTypeeee . ';base64,' . base64_encode($imageDataaaa);

                $html = "<html><body><div style='width:90%;border-radius:20px;border:1px solid black;box-shadow: 5px 10px 18px #888888;padding:5%' ><img src='" . $base64Imagee . "' alt='Image' height='10%' style='float:right;' >" . $users['nom'] . " " . $users['prenom'] . "<div  width='100%'' style='margin-right:35%'>" . $users['n_licence'] . "<br>" . $users['nomm'] . "<br>" . $users['adresseassoc'] . "<br>" . $users['villeassoc'] . " " . $users['zipassoc'] . "<br>" . $users['emailassoc'] . "</p></div><div style='display: flex;flex-direction: row;align-items: top;justify-content: left;margin-top:10%'><img src='" . $base64Imageeee . "'  alt='Image 2' style='' width='18%' height='20%'><div class='text-container' style='width:35%;position:absolute'><p style='font-size: 10px;font-weight: bold;margin: 0 20px;'>26 rue Paul Belmondo<br>75012 Paris - 01.48.10.31.00<br>contact@ffn-naturisme.com</p><br><div style='border-bottom: 1px solid black;margin-top:3%'></div><br><p style='font-size: 10px;position:absolute;font-weight: bold;margin: 0 20px;'>Assurance MAIF 4274207 D<br></p></div><img src='" . $base64Imageee . "'  alt='Image 2' style='width:15%;width: 80px;height: 80px;margin: 0 20px;margin-left:45%'> <img src='" . $base64Image . "'  alt='Image 2' style='width: 80px;height: 80px;margin: 0 20px;'> </div></div></body></html>";

                // Convertir le HTML en 
                $options = new Options();
                $options->setIsRemoteEnabled(true);
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $imageData = $dompdf->output();

                // Enregistrer l'image dans le dossier public/images
                $filesystem = new Filesystem();
                $imagePath = 'images/image_' . $chaine . '.pdf';
                $filesystem->dumpFile($imagePath, $imageData);

                $destinataire = $users['email'];

                if ($destinataire != NULL) {
                    $email = (new Email())
                        ->from("ffn.naturisme.no.reply@gmail.com")
                        ->to($destinataire)
                        ->subject('Fédération française de Naturisme')
                        ->embed(fopen('../public/uploads/94.png', 'r'), 'logo')
                        ->embedFromPath('../public/images/image_' . $users["chaine"] . '.pdf', 'qr')
                        ->html('<img src="cid:logo"><br><br>' . $users["nom"] . ' ' . $users["prenom"] . ',<br><br>Nous vous remercions de l\'intéret que vous portez à la Fédération.<br><br>Votre numéro de licence est : <b>' . $users["n_licence"] . '</b><br><br>Vous pouvez retrouver le guide naturiste <a href="https://ffn-naturisme.com/boutique/produit/guide-naturisme-ffn/">en cliquant ici</a><br><br>Vous pouvez retrouver votre QRCode qui sert de licence (Ne pas oublier une pièce d\'identité)<br>Si votre QRCode ne s\'affiche pas veuillez <a href="https://ffnpro.net/users/qrcode/' . $users["chaine"] . '"><b>cliquer ici</b></a><br>Vous pouvez l\'imprimer sur votre ordinateur en format paysage ou sur téléphone faire une capture d\'écran à présenter<br>Agréable journée !');
                    $mailer->send($email);

                    return $this->render('users/renouvellement.html.twig', [
                        'user' => $user,
                        'licence_new' => $licence_new,
                        'licence_sans_annee' => $licence,
                        'age' => $difference,
                        'lenghtKeyError' => $lenghtKeyError,
                        // 'form' => $formDematerialisation->createView(),
                    ]);
                }
            }
        } elseif ($lenghtKeyError != 11) {

            $user->setNLicence($key);
            $entityManager->flush();

            return $this->render('users/renouvellement.html.twig', [
                'user' => $user,
                'licence_new' => $licence_new,
                'licence_sans_annee' => $licence,
                'age' => $difference,
                'lenghtKeyError' => $lenghtKeyError
            ]);

        } else {

            return $this->render('users/echecRenouvellement.html.twig', [
                'user' => $user,
            ]);
        }
    }


    /**
     * @Route("/all_imprim", name="all_impression", methods={"POST","GET"}, requirements={"id":"\d+"})
     */
    public function allimprime(UsersRepository $usersRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $count = $usersRepository->countImprimed();

        return $this->render('base.html.twig', [
            'count' => $count,
        ]);
    }
}