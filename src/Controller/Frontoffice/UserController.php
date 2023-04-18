<?php

namespace App\Controller\Frontoffice;


use App\Entity\Rate;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\RatingType;
use App\Repository\RateRepository;
use App\Repository\UserRepository;
use App\Repository\QuoteRepository;
use App\Repository\AvatarRepository;
use App\Security\KaamelottAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class UserController extends AbstractController
{

                                                        // FAVORITE PARTS //
    /**
     * @Route("/favoris/{userId}", name="app_favorites_user")
     */
    public function index(Security $security, $userId, UserRepository $userRepository,QuoteRepository $quoteRepository): Response
    {

        $favoritesQuotesId = $userRepository -> favoritesQuotes($userId);
        
        return $this->render('frontoffice/user/indexFav.html.twig', [
            "allFav" => $favoritesQuotesId,
        ]);
    }

    /**
     * @Route("/favorite-quotes/add/{quoteId}", name="user_add_favorite_quote")
     */
    public function addFavorite(EntityManagerInterface $entityManager, Security $security, int $quoteId, QuoteRepository $quoteRepository): Response
    {
        
        // je récupère le User s'il est connecté
        $user = $security->getUser();
        // je récupère la quote grâce à son id
        $quote = $quoteRepository->find($quoteId);
        // dd($user);
        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur doit être connecté pour ajouter une citation en favori.');
        }

        if (!$quote) {
            throw $this->createNotFoundException('La citation demandée n\'existe pas.');

        }
        
        $user->addFavoriteQuote($quote);
        $entityManager->flush();

        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * @Route("/favorite-quotes/remove/{quoteId}", name="user_remove_favorite_quote")
     */
    public function removeFavorite(EntityManagerInterface $entityManager, Security $security, int $quoteId, QuoteRepository $quoteRepository): Response
    {
        
        // je récupère le User s'il est connecté
        $user = $security->getUser();
        // je récupère la quote grâce à son id
        $quote = $quoteRepository->find($quoteId);
       
        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur doit être connecté pour retirer une citation en favori.');
        }

        if (!$quote) {
            throw $this->createNotFoundException('La citation demandée n\'existe pas.');

        }
        
        // $users = $quoteRepository -> find($quote) -> getUsers();
        $user->removeFavoriteQuote($quote);
        $entityManager->flush();

        

        return $this->redirect($_SERVER['HTTP_REFERER']);
    }


                                                        // PROFILE PARTS //

    /**
     * @Route("/inscription", name="app_register")
     */
    public function register(AvatarRepository $avatarRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, KaamelottAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        $user->setRoles(["ROLE_USER"]);

        $avatar = $avatarRepository->find(5);

        $user->setAvatar($avatar);

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
        }   

        return $this->render('frontoffice/user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    
    /**
     * User's profile
     *
     * @Route("/utilisateur/{id}/profil", name="user_read_profile", requirements={"id" = "\d+"})
     */
    public function read(UserRepository $userRepository, $id, User $user) : Response
    {
        if($this->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé ici.");
        }

        $user = $userRepository->find($id);

        return $this->render('frontoffice/user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit user's profile
     * 
     * @Route("/utilisateur/{id}/edition-profil", name ="user_edit_profile", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, UserRepository $userRepository) : Response
    {

        if($this->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé ici.");
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $password = $form->get("plainPassword")->getData();
            $hashedpassword = $userPasswordHasherInterface->hashPassword($user, $password);
            $user->setPassword($hashedpassword);
            
            $userRepository->add($user, true);

            return $this->redirectToRoute("user_read_profile", ['id' => $user->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('frontoffice/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);

    }


                                                        // RATING PARTS //

    /**
     * @Route("/quote-rating/add/{quoteId}", name="user_add_rating_quote")
     */
    public function addRating(EntityManagerInterface $entityManager, Security $security, Request $request, int $quoteId, QuoteRepository $quoteRepository, RateRepository $rateRepository): Response
    {
        
        // je récupère le User s'il est connecté
        $user = $security->getUser();
        
        // je récupère la quote grâce à son id
        $quote = $quoteRepository->find($quoteId);

        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur doit être connecté pour noter une citation en favori.');
        }

        if (!$quote) {
            throw $this->createNotFoundException('La citation demandée n\'existe pas.');

        }

        $newRating = new Rate();

        $newRating ->setUser($user);
        $newRating -> setQuote($quote);
        $form = $this->createForm(RatingType::class, $newRating);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            //! condition pour noter qu'une seule fois la citation
            
            //  DONE: persist + flush
            
            $newRating ->setUser($user);
            $newRating->setQuote($quote); //on a la note du form
            // dd($newRating);
            $entityManager->persist($newRating);
            $entityManager->flush();

            

            //  DONE: recalcul du rating
           
            $averageRatingQuote = $rateRepository -> averageRating($quoteId);
          
            $quote -> setRating($averageRatingQuote);
            $entityManager->persist($quote);
            $entityManager->flush();

            return $this->redirectToRoute('default');
        }

        return $this->renderForm('frontoffice/user/formRating.html.twig', [
            "formulaire" => $form,
            "quote" => $quote
        ]);
    }






}
