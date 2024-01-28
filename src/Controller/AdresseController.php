<?php

namespace App\Controller;
use App\Entity\Adresses;
use App\Form\AdressesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdresseController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/adresse', name: 'app_adresse')]
    public function index(Request $request): Response
    {
        $adresse = new Adresses();
        $form = $this->createForm(AdressesType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adresse->setUser($this->getUser());
            $adresse->setCreatedAt(new \DateTimeImmutable());
            $adresse->setUpdatedAt(new \DateTimeImmutable());

            $this->em->persist($adresse);
            $this->em->flush();

            $this->addFlash('success', 'Votre adresse a bien été enregistrée');
            return $this->redirectToRoute('app_adresse');
        }
        return $this->render('espace/adresses.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
