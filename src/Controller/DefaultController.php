<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index()
    {
        if ($this->getUser()) {
            return $this->render('default/index.html.twig');         

        } else {
            return $this->redirectToRoute('/login');
        }   

    }
}

