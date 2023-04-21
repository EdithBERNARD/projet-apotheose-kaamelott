<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\PlayQuizz;
use App\Repository\PlayQuizzRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/user", name="app_api_user")
     */
    public function index(): Response
    {
        return $this->render('api/user/index.html.twig', [
            'controller_name' => 'User',
        ]);
    }

    /**
     * @Route("/api/playquizz/{id}", name="app_api_playquizz_edit", methods={"PUT", "PATCH"}, requirements={"id"="\d+"})
     */
    public function edit(
        Genre $genre = null, 
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
        )
    {
        // TODO : on modifie une entité
        // 1. l'entité à modifier : paramètre de route
        if ($genre === null){
            // le paramConverter n'a pas trouvé l'entité : 404
            return $this->json("Genre non trouvé", Response::HTTP_NOT_FOUND);
        }
        // 2. les informations de la requete
        $jsonContent = $request->getContent();

        // 3. je déserialize
        try {
            $serializer->deserialize(
                $jsonContent,
                Genre::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $genre]
            );
        } catch (\Throwable $e){
            // notre exception est dans $e
            // dd($e);
            // TODO avertir l'utilisateur
            return $this->json(
                // 1. le message d'erreur
                $e->getMessage(),
                // 2. le code approprié : 422
                Response::HTTP_UNPROCESSABLE_ENTITY

            );
        }
        // il faut faire l'association entre TOUTE les propriétés
        // là encore ça va 1 prop, mais avec Movie :'(
        // $genre->setName($genreUpdate->getName());
        // * on utilise donc une option du serializer pour qu'il nous mettes à jour notre entité
        // ? https://symfony.com/doc/current/components/serializer.html#deserializing-in-an-existing-object
        // un peu comme le handleRequest d'un formulaire

        // TODO : on valide les données avant de persist/flush
        // ? https://symfony.com/doc/5.4/validation.html#using-the-validator-service
        $listError = $validator->validate($genre);

        if (count($listError) > 0){
            // On a des erreurs de validation
            return $this->json(
                // 1. le contenu : la liste des erreurs
                $listError,
                // 2. un code approprié : 422
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // * ici mon objet $genre a été modifié
        // un flush est tout est bon
        $entityManager->flush();
        
        // TODO : return json
        return $this->json(
            // aucune donnée à renvoyer, puisque c'est juste une mise à jour
            // à voir avec votre dev front
            // ! si le code 204 est utilisé aucune donnée ne sera envoyé
            null,
            // le code approprié : 204
            Response::HTTP_NO_CONTENT
        );
    }

}
