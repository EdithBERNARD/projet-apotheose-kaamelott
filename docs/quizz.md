# Petit récap sur le travail effectué pour le quizz

( voici comment je l'ai compris)

dans notre BDD 
on a une table quizz, une question et une table pivot qui rassemble l'id du quiz, l'id de l'utilisateur et le score correspondant.

## coté js (dossier public) + CSS

### fichier CSS

un fichier CSS a été créé spécialement pour la partie quizz. 

### dans  app.js

création de l'url nous permettant de dialoguer avec notre API 

création d'une fonction initApp() => appel de la fonction : showQuestion(0)

appel de la fonction initApp()

### dans main.js

initialisation de toutes les variables dont on aura besoin
puis le code a été divisé en différentes fonctions 

#### getQuestions

c'est un fonction async , nous utilisons await avec fetch pour que quand la prommesse est résolue ( on effectue une requête HTTP ), 

la réponse est mise dans req
req.json lit la reponse HTTP en json; et met la réponse dans quizz
puis dans questions on mets les questions du quizz


#### getQuizz

c'est un fonction async , nous utilisons await avec fetch pour que quand la prommesse est résolue ( on effectue une requête HTTP ), 
dans quiz on récupère les donnée de la bdd
c'est l'id du quizz qui nous interresse ici

#### next

cette fonction asyncrone permet de passer à la question suivante 
on compare l'index au nb de question si c'était la dernière question on affiche le résultat (on apelle la fonction résultats)

sinon on donne le score et on rapelle la question suivante avec current_index+1 en affichant le nb de bonnes réponses / le nb de questions total

#### resultats

quand le quizz est fini on fait appel à getquizz et à getquestions qui nous permet d'avoir les infos : on parle de tel quizz et on a tant de question

userID nous donne l'id de l'utilisateur 
alors on affiche le score

et on l'envoie dans la bdd sur le profil de l'uilisateur (l'id du quizz, le score et l'id de l'utilisateur)
avec then (prommesse ok )on affiche le mess de succes(bien reçu dans la BDD) sinon avec catch on affiche un message d'erreur

puis on propose de refaire le quiiz avec un btn qui au clic apelle la fonction startAgainQuizz()

#### compare

cette fonction compare la reponse de l'utilisateur à la bonne réponse, si c'est la même on augmente le score de 1 puis dans tous les cas on appel la fonction next

#### showQuestion (index)

l'appel de cette fonction se fait dans le initApp, elle permet d'afficher la question de l'index demandé c'est une fonction asynchrone avec :
```js
let questions = await this.getQuestions();
```
await suspend l'execution de la fonction jusqu'a ce que la promesse soit résolue
quand on a les questions (grace à getQuestions) 
et que l'utilisateur a cliqué sur une des propositions de réponse 
avec la fonction compare on check si la réponse est bonne

#### startAgainQuizz

d'abord on remet le score à zéro puis appel showQuestion à l'index 0

## dossier SRC

### controller pour l'API (csr Controller Api)

#### PlayQuizzController

browse - on a les infos de tout les quizz qui ont été joué avec id quizz, id user et score

read - on a les infos concernant le quiz joué qui à l'id {id}

Add - ajoute à la bdd les donnés du nouveau quiz qui a été joué avec dans la fonction JS resultat
```js
fetch(`${newUrl}api/play/quizz/add`, {
	method: 'POST',
	body: JSON.stringify({
		"quizz":quizzId,
		"score":score,
		"user": userId
	  })
	})
```


#### QuizzController

read premet d'avoir toutes les infos : les questions et les choix de réponse et la bonne réponse

### controller frontoffice (csr Controller Frontoffice)

#### QuizzController

on a une sécu avec notre voterquizz nous assurant qu'un visiteur anonyme ne puisse pas acceder au quizz
nous permet d'acceder à la page du quizz en quetion redirection sur la vue quizz/read.html.twig

#### UserController

read - permet de lire/afficher le dernier score de chaque quizz

## entité

on prépare nos controller API
```php  
    /**
     * @ORM\ManyToOne(targetEntity=Quizz::class, inversedBy="playQuizz")
     * @Groups({"playQuizz_browse", "playQuizz_read"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $quizz;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="playQuizz")
     * @Groups({"playQuizz_browse", "playQuizz_read"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"playQuizz_browse", "playQuizz_read"})
     */
    private $score;
```

## repository 

requète sql pour récuper le dernier score

###

## list quizz en global 

on met quizz en global pour pouvoir acceder au differnts quizz via la barre de navigation (si j'ai besoin de plus de détail j'ai le global.md)

## templates twig - Frontoffice

### quizz

on affiche le titre du quizz et le code HTML permettant d'utiliser notre code JS pour afficher le quiz en dynamique 

### user Profile

on récupère les résultats des quiz et on les affiche sur la page profil de l'utilisateur connecté

### navigation

si on est connecté on affiche la liste des quiz et on peux y acceder (car on a mis les quizz en global)

### base.html.twig

on n'oublie pas de faire appel à la feuille de style CCS du quizz

```twig
<link href="{{ asset('css/styles_quizz.css')}}" rel="stylesheet"> 
```
ni au scripts JS qui nous permet de jouer aux quizz
```twig
<!-- Scripts -->
            {% if app.request.pathinfo starts with '/quizz' %}
            
                <script src="{{ asset('main.js')}}"></script>
                <script src="{{ asset('js/app.js')}}"></script>  
                              
            {% endif %}
```

