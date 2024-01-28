<?php
namespace App\Controller; // Déclaration de l'espace de nom

use App\Entity\User;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InscriptionController extends AbstractController
{
    private EntityManagerInterface $em; // Déclaration de la propriété $em de type EntityManagerInterface

    public function __construct(EntityManagerInterface $em) // Injection de dépendance
    {
        $this->em = $em; // Initialisation de la propriété $em
    }
    #[Route('/inscription', name: 'app_inscription')]
    public function index(Request $request): Response
    {
        $user = new User(); // Création d'un nouvel utilisateur
        $form = $this->createForm(InscriptionType::class, $user); // Création du formulaire d'inscription

        $form->handleRequest($request); // Gestion de la requête

        if ($form->isSubmitted() && $form->isValid()) { // Vérification de la soumission du formulaire et de sa validité
            // Récupération des données du formulaire
            $user = $form->getData(); // Stockage des données du formulaire dans l'objet $user (en memoire)

            // On vérifie l'existence de l'adresse e-mail de l'utilisateur
            $userExist = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if (!$userExist) {
                // Hashage du mot de passe
                $user->setPassword(password_hash($user->getPassword(), PASSWORD_ARGON2I));

                //dd($user);

                // Enregistrement de l'utilisateur dans la base de données
                $this->em->persist($user); // Permet de préparer une requête SQL
                $this->em->flush(); // Exécute la requête SQL

                $this->addFlash('success', 'Votre compte a été créé avec succès !');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Cette adresse e-mail est déjà utilisée !');
            }
        }

        return $this->render('inscription/index.html.twig', [
            'form' => $form->createView(), // Création de la vue du formulaire
        ]);
    }
}
