<?php

namespace App\Controller;

use App\Entity\Associations;
use App\Form\QrCodeType;
use App\Repository\UsersRepository;
use App\Services\QrCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Endroid\QrCode\Writer\Result\PngResult;

class QrCodeController extends AbstractController
{
    /**
     * @Route("/qrcode/show/{chaine}", name="qr_code")
     */
    public function index(Request $request, QrcodeService $qrcodeService,$chaine,UsersRepository $usersRepository): Response
    {
            $users = $usersRepository->findByChaine($chaine);
            $qrCode = $qrcodeService->qrcode($chaine);
            
   
            return $this->render('qrcode/index.html.twig', [
                // 'form' => $form->createView(),
                'qrcode' => $qrCode,
                'users' => $users,
            ]);
        
    }
}
