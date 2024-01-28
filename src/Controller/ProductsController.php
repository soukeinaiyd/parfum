<?php
namespace App\Controller;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductsController extends AbstractController
{
    private EntityManagerInterface $em; // Déclaration de la propriété $em de type EntityManagerInterface
    public function __construct(EntityManagerInterface $em) // Injection de dépendance
    {
        $this->em = $em; // Initialisation de la propriété $em
    }
    #[Route('/products', name: 'app_products')]
    public function index(): Response
    {
        $products = $this->em->getRepository(Products::class)->findAll();

        if(!$products){
            throw $this->createNotFoundException('Aucun produit trouvé');
        }
        return $this->render('products/index.html.twig', [
            'products' => $products,
        ]);
    }
    //On affiche les produits
    #[Route('/products/{slug}', name: 'show_product')]

   // #[Route('/products/slug', name: 'show_product')]
    public function show($slug): Response
    {
        $product = $this->em->getRepository(Products::class)->findOneBy(['slug' => $slug]);
        //if (!$product) {
        //return $this->redirectToRoute('products');
       //  }
         return $this->render('products/show.html.twig', [
            'product' => $product,
        ]);
    }
}

