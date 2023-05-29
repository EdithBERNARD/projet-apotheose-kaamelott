// url API

// I retrieve the url of my current page
//* location.href renvoi l'url de la page actuelle
const url= document.location.href;
// I remove the url from my page and I only keep the last information of the url
//* extrait la dernière partie de l'URL ici l'identifiant de la page je ne garde que la fin
const idPage = url.substring(url.lastIndexOf( "/" )+1 );
// I remove the end of the url
//* je ne garde que le début de l'url ( en gros  on replace "quizz/idpage" par "")
const newUrl = url.replace("quizz/" + idPage,"");

// I concatenate the url of the api with the id of my page
//* puis on concaténete pour créer l'adresss souhaité c'est à dire l'api pour récuperer la repose JSON ici les questions/reponse du quizz de la 
const urlQuizz = newUrl + "api/quizz/" + idPage;


function initApp(){
   
   showQuestion(0);
}

initApp();
 