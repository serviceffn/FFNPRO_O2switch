<?php

namespace App\Controller;


use App\Form\ContactFormType;
use App\Repository\Ticketsrepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;




/**
 * @Route("/qrcode/choix")
 */
class QRCodeMultiple extends AbstractController
{
    /**
     * @Route("/", name="qrcode_choix", methods={"GET","POST"})
     */
    public function index(Request $request): Response
    {
        
       
         
            return $this->render('qrcode/multiple.html.twig', [
            ]);
     
    }
    
   
    


}