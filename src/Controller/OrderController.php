<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/order', name: 'app_order')]
    public function index(SessionInterface $session): Response
    {
        $user = $this->getUser();
        if (!$user || $user->getAdresses()->isEmpty()) {
            return $this->redirectToRoute('app_adresse');
        }

        $order = new Order();
        $adresses = $user->getAdresses()->first();
        $date = new \DateTime;
        $order->setStatus(0);

        
        $adresseLivraison = $adresses->getRue() . ', ' . $adresses->getVille() . ', ' . $adresses->getCodePostal() . ', ' . $adresses->getPays();
        $user = $this->getUser();
        $order->setUser($user);
        $order->setStatus(0);
        $order->setCoutLivraison(0);
        $order->setAdresseLivraison($adresseLivraison);
        $order->setAdresseFacturation($adresseLivraison);
        $order->setOrderedAt(new \DateTimeImmutable());

        $cartItems = $session->get('cart', []);
        $total = 0;

        foreach ($cartItems as $cartItem) {
            $orderDetail = new OrderDetails();
            $orderDetail->setCommande($order);
            $orderDetail->setProduct($cartItem['product']);
            $orderDetail->setQuantity($cartItem['quantity']);
            $orderDetail->setPrix($cartItem['product']->getPrix());

            $total += $cartItem['quantity'] * $cartItem['product']->getPrix();

            $this->em->persist($orderDetail);
            $order->addOrderDetail($orderDetail);
        }

        $order->setTotal($total);
        $this->em->persist($order);
        $this->em->flush();

        $session->remove('cart');

        return $this->redirectToRoute('app_order_detail', ['id' => $order->getId()]);
    }


    #[Route('/order-details/{id}', name: 'app_order_detail')]
    public function detail(int $id): Response
    {
        $order = $this->em->getRepository(Order::class)->find($id);

        if (!$order) {
            $this->redirectToRoute('products');
        }

        return $this->render('order/detail.html.twig', [
            'order' => $order,
            'orderDetails' => $order->getOrderDetails()
        ]);
    }

     #[Route('/mes-commandes', name: 'app_mes_commandes')]
    public function mesCommandes(): Response
    {
        $user = $this->getUser();

        // On récupère les commandes de l'utilisateur avec les détails des commandes
        $commandes = $this->em->getRepository(Order::class)->findBy(['user' => $user]);

        foreach ($commandes as $commande) {
            foreach ($commande->getOrderDetails() as $detail) {
                $this->em->initializeObject($detail->getProduct());
            }
        }


        return $this->render('espace/my_orders.html.twig', [
            'commandes' => $commandes,

        ]);
    }
}