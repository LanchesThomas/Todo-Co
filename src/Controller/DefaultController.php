<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    /**
     * Displays the homepage or redirects to the login page if not authenticated.
     *
     * @return Response
     */
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

