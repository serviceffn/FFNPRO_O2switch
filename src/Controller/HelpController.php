<?php

namespace App\Controller;

use App\Entity\Tickets;
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
 * @Route("/tickets")
 */
class HelpController extends AbstractController
{
    /**
     * @Route("/", name="help_index", methods={"GET","POST"})
     */
    public function index(Request $request,EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
         $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
         
         $form = $this->createForm(ContactFormtype::class);
         $handle = $form->handleRequest($request);
         
         if($form->isSubmitted() && $form->isValid()){
         
            $post = $form->getData();
            $destinataire = $form->getData()->getDestinataire();
            $envoyeur = $form->getData()->getEnvoyeur();
            $message = $form->getData()->getMessage();
            
            $tickets = new Tickets();
            $tickets->setDestinataire($destinataire);
            $tickets->setEnvoyeur($envoyeur);
            $tickets->setMessage($message);
            
            $entityManager->persist($tickets);
            $entityManager->flush();
            
            if($destinataire == 'secreteriat'){
            
            $email = (new Email())
                    ->from("ffn.naturisme.no.reply@gmail.com")
                    ->to("contact@ffn-naturisme.com")
                    ->subject('Contact Via FFNPRO - '.$envoyeur.' ')
                    ->html($message);
                    $mailer->send($email);
            }else{
            $email = (new Email())
                    ->from("ffn.naturisme.no.reply@gmail.com")
                    ->to("service-informatique@ffn-naturisme.com")
                    ->subject('Contact Via FFNPRO - '.$envoyeur.' ')
                    ->html($message);
                    $mailer->send($email);
            }
        }
       
         
            return $this->render('aide/index.html.twig', [
                'form' => $form->createView(),
            ]);
     
    }
    
    /**
     * @Route("/assoc", name="help_assoc", methods={"GET","POST"})
     */
    public function helpAssoc ( Request $request ): Response
    {
         $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
         
       
         
            return $this->render('aide/index.html.twig', [
  
            ]);
     
    }
    


}