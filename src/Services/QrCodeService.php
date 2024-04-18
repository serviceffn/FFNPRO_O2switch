<?php

namespace App\Services;

use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHight;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Symfony\Component\Form\Test\FormBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class QrCodeService extends AbstractController
{

    public function __construct(BuilderInterface $builder){

        $this->builder = $builder;

    }

    public function qrcode($chaine){
        $url = 'https://ffnpro.net/users/qrcode/';
        $path = dirname(__DIR__,2).'/public/uploads/logo.png';
    
        $result = $this->builder
            ->data($url.$chaine)
            // ->errorCorrectionLevel(new ErrorCorrectionLevelHight())
            ->size(400)
            ->margin(10)
            ->build();
    
            $result->saveToFile(dirname(__DIR__,2).'/public/qr-code/'.$chaine.'.png');

        return $result->getDataUri();
    }
}