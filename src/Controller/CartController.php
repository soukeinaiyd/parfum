<?php
namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItems;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    #[Route('/cart', name: 'cart')]
    public function index(): Response
    {
        // On affiche le panier de l'utilisateur
        $user = $this->security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('app_login');
        }

        $cart = $this->em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => 'active']); // On récupère le panier de l'utilisateur
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->em->persist($cart);
            $this->em->flush();
        }

        $cartItems = $this->em->getRepository(CartItems::class)->findBy(['cart' => $cart]);

        // Calcul du total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->getQuantity() * $item->getProduct()->getPrix();
        }
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/add-to-cart/{slug}', name: 'add_to_cart')]
    public function addToCart($slug): Response
    {
        // On récupère le produit par son slug
        $product = $this->em->getRepository(Products::class)->findOneBy(['slug' => $slug]);

        // Gestion si le produit n'est pas trouvé
        if (!$product) {
            return $this->redirectToRoute('produits');
        }

        // Récupérer l'utilisateur actuel
        $user = $this->security->getUser();

        // Trouver le panier de l'utilisateur ou en créer un nouveau
        $cart = $this->em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => 'active']);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setCreatedAt(new \DateTimeImmutable());
            $cart->setUpdateAt(new \DateTimeImmutable());
            $cart->setStatus('active');
            $this->em->persist($cart);
        }

        // Vérifier si le produit est déjà dans le panier
        $cartItem = $this->em->getRepository(CartItems::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartItem) {
            // Incrémenter la quantité si l'article existe déjà
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        } else {
            // Créer un nouveau CartItem pour un nouveau produit
            $cartItem = new CartItems();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $cartItem->setPrix($product->getPrix());

            $cartItem->setAddedAt(new \DateTimeImmutable());

            $this->em->persist($cartItem);
        }

        $this->em->flush();

        // Rediriger vers la page du produit
        //return $this->redirectToRoute('show_produit', ['slug' => $slug]);
        return $this->redirectToRoute('cart');
    }

    #[Route('/remove-from-cart/{cartItemId}', name: 'remove_from_cart')]
    public function removeFromCart($cartItemId): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->security->getUser();

        // Trouver l'article dans le panier
        $cartItem = $this->em->getRepository(CartItems::class)->findOneBy([
            'id' => $cartItemId,
            // On vérifie que l'article appartient bien au panier de l'utilisateur
            'cart' => settype($user, 'object') ? $this->em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => 'active']) : null
        ]);

        // Si l'article n'existe pas ou n'appartient pas au panier de l'utilisateur, gérer l'erreur
        if (!$cartItem) {
            throw $this->createNotFoundException('Article non trouvé dans le panier.');
        }

        // Supprimer l'article du panier
        $this->em->remove($cartItem);
        $this->em->flush();

        // Rediriger vers la page du panier ou une autre page appropriée
        return $this->redirectToRoute('cart');
    }

    // Méthode decreaseQuantity() et increaseQuantity()
    // pour diminuer ou augmenter la quantité d'un article dans le panier
    #[Route('/decrease-quantity/{cartItemId}', name: 'decrease_quantity')]
    public function decreaseQuantity($cartItemId): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->security->getUser();

        // Trouver l'article dans le panier
        $cartItem = $this->em->getRepository(CartItems::class)->findOneBy([
            'id' => $cartItemId,
            // On vérifie que l'article appartient bien au panier de l'utilisateur
            'cart' => $user ? $this->em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => 'active']) : null
        ]);

        // Si l'article n'existe pas ou n'appartient pas au panier de l'utilisateur, gérer l'erreur
        if (!$cartItem) {
            throw $this->createNotFoundException('Article non trouvé dans le panier.');
        }

        // Si la quantité est supérieure à 1, on la diminue de 1
        if ($cartItem->getQuantity() > 1) {
            $cartItem->setQuantity($cartItem->getQuantity() - 1);
        }

        // Enregistrer les changements
        $this->em->flush();

        // Rediriger vers la page du panier ou une autre page appropriée
        return $this->redirectToRoute('cart');
    }

    #[Route('/increase-quantity/{cartItemId}', name: 'increase_quantity')]
    public function increaseQuantity($cartItemId): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->security->getUser();

        // Trouver l'article dans le panier
        $cartItem = $this->em->getRepository(CartItems::class)->findOneBy([
            'id' => $cartItemId,
            // On vérifie que l'article appartient bien au panier de l'utilisateur
            'cart' => $user ? $this->em->getRepository(Cart::class)->findOneBy(['user' => $user, 'status' => 'active']) : null
        ]);

        // Si l'article n'existe pas ou n'appartient pas au panier de l'utilisateur, gérer l'erreur
        if (!$cartItem) {
            throw $this->createNotFoundException('Article non trouvé dans le panier.');
        }

        // On augmente la quantité de 1
        $cartItem->setQuantity($cartItem->getQuantity() + 1);

        // Enregistrer les changements
        $this->em->flush();

        // Rediriger vers la page du panier ou une autre page appropriée
        return $this->redirectToRoute('cart');
    }

}
