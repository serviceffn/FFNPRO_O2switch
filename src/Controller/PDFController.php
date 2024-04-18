<?php

namespace App\Controller;

use App\Entity\Associations;
use App\Repository\AssociationsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pdf")
 */
class PDFController extends AbstractController
{
    /**
     * @Route("/", name="indexx", methods={"GET","POST"})
     */
    public function index()
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->setIsRemoteEnabled(true);  
        $pdfOptions->set('defaultFont', 'Arial');
        // $pic = "<img src='../../public/uploads/midi.jpg'>"  ;
    
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('pdf/index.html.twig', [
            'title' => "Welcome to our PDF Test",
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }

    /**
     * @Route("/pdf/{id}", name="pdfpdf", methods={"GET","POST"})
     */
    public function indexx($id, UsersRepository $usersRepository, AssociationsRepository $associationsRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $usersRepository->printLicence($id);
        $update = $usersRepository->updateIsImprimedId($id);

        return $this->render('pdf/index.html.twig',[
            'user' => $user,
        ]);
    }
}