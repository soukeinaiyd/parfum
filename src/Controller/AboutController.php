<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'about')]
    public function index(): Response
    {

        $about = [
            'name' => 'mon nom est:',
            'post' => 'developpement web et mobile',
            'description' => 'developpeur full stuck',

            'contact'=> [
                'email' =>'souka@gmail.com',
                'phone' => '06 12 34 56 78',
            ],
        ];
        /*return $this->render('about/index.html.twig', [
            'controller_name' => 'AboutController',
        ]);*/


        return $this->render('about/index.html.twig', [
            'about' => $about,
        ]);
    }
}
