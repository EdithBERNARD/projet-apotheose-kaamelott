<?php

namespace App\Controller\Frontoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RatingController extends AbstractController
{
    /**
     * @Route("/rating/{id}", name="app_frontoffice_rating_add", requirements={"id"="\d+"})
     */
    public function index(): Response
    {



        return $this->render('frontoffice/rating/index.html.twig', [
            'controller_name' => 'RatingController',
        ]);


             // TODO : ajouter un review sur un movie
        // * Mes besoins
        // 1. du film, via la route, donc parkmètre/paramètre de route avec {id}
        // 2. j'affiche le formulaire
        // 3. gestion du formulaire remplit : POST, donc restreindre les méthodes
        // 4. Add review, ReviewRepository
        // 5. une redirection vers la route app_home_show
        //$movie = $movieRepository->find($id)
        
        $newReview = new Review();
        $form = $this->createForm(ReviewType::class, $newReview);

        // on récupère les infos du formulaire, et on met à jour notre entité $newReview
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // TODO : persist + flush
            // il nous manque l'association avec le film
            $newReview->setMovie($movieFromRoute);

            // TODO : recalcul du rating

            $reviewRepository->add($newReview, true);

            // redirection
            return $this->redirectToRoute('app_home_show', ["id" => $movieFromRoute->getId(),"slug" => $movieFromRoute->getSlug()]);
        }

        return $this->renderForm('review/index.html.twig', [
            "formulaire" => $form,
            "movie" => $movieFromRoute
        ]);
    }
}
