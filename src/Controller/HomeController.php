<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $a = 2;
        $donnees = [
            'title' => 'Bienvenue chez nous',
            'message' => 'Vous pouvez inscrire à notre newsletter pour recevoir des news de notre sites',
        ];
        /*return $this->render('home/index.html.twig', [
            'a' => $a, 
        ]);*/
        return $this->render('home/index.html.twig', [
            'donnees' => $donnees,
        ]);

}

}
