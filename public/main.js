//
// main.js
//

// variables initialization
var current_index = 0 ;
let score = 0;

let start = document.getElementById("start");
let scoreContainer = document.getElementById("score-container");

let quizElement = document.getElementById("questions-container");
let answers = document.getElementById("answers");
let resultatContainer = document.getElementById("resultat-container");
let resultat = document.getElementById("resultat");
let scoreDiv = document.getElementById("score");
let profilDiv = document.getElementById("profil");
let	againDiv = document.getElementById("again");
let titleElement = document.getElementById("title");
let userInput = document.getElementById("userId");

// var questions = Object;
var questions = {};

// the getQuestions function is used to retrieve information from the API
//* on utilise cette fonction pour récuperer les info à partir de l'API
async function getQuestions() {

    const req = await fetch(`${urlQuizz}`); 
    const quizz = await req.json();
    const questions = quizz.questions;
  
	return questions;

}

// the getQuizz function is used to retrieve information from the API
//* on utilise cette fonction pour récuperer les info à partir de l'API
async function getQuizz() {

    const req = await fetch(`${urlQuizz}`); 
    const quizz = await req.json();
	const quizzId = quizz.id;
	return quizzId;

}

// The next function allows you to move on to the next question. At the last question answered, the score is displayed with the resultats function
//* avec next on passe à la ? suivante , resultat affiche le score
async function next(){
	let questions = await this.getQuestions();
	//* si on notre index+1 = le nb de question on fournit le résultat final sinon on donne le score actuel / le nb de question et on affiche la question
    if (current_index+1 == questions.length){
		resultats();
    }else{
		scoreContainer.innerHTML = "Score: " + score + " / " + questions.length;
        showQuestion((current_index+1));
    }
}

// the resultats function allows the display of the score and the button to start again and to register the score in db
//* cette fonction affice le resultat et enregistre le score dans la bdd
async function resultats(){
	let quizzId = await this.getQuizz();
	let questions = await this.getQuestions();
	let userId = userInput.value;
	console.log(userId);
	scoreDiv.innerHTML = "";
	titleElement.innerHTML="";
	answers.innerHTML="";
	quizElement.style.visibility = 'hidden';

	resultatContainer.style.visibility = 'visible';
	scoreDiv.style.visibility = 'visible';
    resultat.style.visibility = 'visible';
	
	//* pour affiche le score
	// display button score (top)
	scoreContainer.innerHTML = "Score: " + score + " / " + questions.length;
	// display score (bottom)
	scoreDiv.innerHTML = `Vous avez ${score} bonnes réponses sur ${questions.length}`;
	
	//* envoie le score sur la page profil de l'utilisateur 
	//* fetch permet d'effectuer des requètes HTTP ici on envoie en POST les 3 info suivantes l'id du quizz,
	//* le score obtenu et l'id de l'utilisateur)
	// Send score to profil user (->backoffice -> BDD)
	fetch(`${newUrl}api/play/quizz/add`, {
	method: 'POST',
	body: JSON.stringify({
		"quizz":quizzId,
		"score":score,
		"user": userId
	  })
	})
	//* la requète est réussie on affiche le message de succes
	.then(response => {
		profilDiv.innerHTML = "Votre score a été ajouté dans votre profil";
	})
	//* sinon mess d'erreur
	.catch(error => console.error(error));

	againDiv.innerHTML = "<button  type='submit'  id='againButton' onclick=\"startAgainQuiz()\";>Recommencer</button>";
	
}

//* cette fonction permet de comparer la réponse donnée avec la bonne réponse 
// the compare function compares the player's answer with the correct answer
function compare  (a,b){

	//* on icrémente de 1 si a est inclu dans b
	if (b.includes(a)){
		score +=1;
	}
	//* dans tous les cas on passe à la question suivante
 	next();
}

// the showQuestion function displays the question
//* cette fonction affiche la question et le choix de réponse
async function showQuestion(index){
	
	current_index = index;
    let questions = await this.getQuestions();
	
	start.style.visibility = 'hidden';
	resultatContainer.style.visibility = 'hidden';
	quizElement.style.visibility = 'visible';
	
	titleElement.innerHTML = questions[index].title;

	answers.innerHTML = "";
	//* au clic on appelle la fonction compare(repose x,bonne réponse)
	answers.innerHTML += "<button type='submit' class='answer' value='"+questions[index].answer1+"' onclick=\"compare(\'"+questions[index].answer1+"\',\'"+questions[index].goodAnswer+"\');\" > "+questions[index].answer1+"</button>"; 
    answers.innerHTML += "<button type='submit' class='answer' value='"+questions[index].answer2+"' onclick=\"compare(\'"+questions[index].answer2+"\',\'"+questions[index].goodAnswer+"\');\" > "+questions[index].answer2+" </button>"; 
    answers.innerHTML += "<button type='submit' class='answer' value='"+questions[index].answer3+"' onclick=\"compare(\'"+questions[index].answer3+"\',\'"+questions[index].goodAnswer+"\');\" > "+questions[index].answer3+"</button>"; 
    answers.innerHTML += "<button type='submit' class='answer' value='"+questions[index].answer4+"' onclick=\"compare(\'"+questions[index].answer4+"\',\'"+questions[index].goodAnswer+"\');\" > "+questions[index].answer4+"</button>";
    
}

// the startAgainQuiz function allows you to restart the quiz
//* permet de recommencer le quizz
function startAgainQuiz(){
	
	resultatContainer.style.visibility = 'hidden';
    resultat.style.visibility = 'hidden';
	scoreDiv.style.visibility = 'hidden';
	
	scoreContainer.innerHTML = "Score: 0";
	score =0;
	showQuestion(0);	
}


  



