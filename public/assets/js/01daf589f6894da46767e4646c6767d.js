let create_ville = document.getElementById("ville_compte_create");
let create_postal = document.getElementById("postal_compte_create");
let create_tel = document.getElementById("tel_compte_create");
let erreur_tel_compte = document.getElementById("erreur_tel_compte");
let erreur_postal_compte = document.getElementById("erreur_postal_compte");
let erreur_ville_compte = document.getElementById("erreur_ville_compte");
const pwdOne = document.getElementById("register_password_first");
const pwdTwo = document.getElementById("register_password_second");
let erreur_pwd = document.getElementById("erreur_pwd");

function onAddAdresseWhenCreate(){
    erreur_ville_compte.innerText = "";
    erreur_postal_compte.innerText = "";
    erreur_tel_compte.innerText = "";
    erreur_pwd.innerText = "";
    if(!create_ville.value.match(/^[a-zA-ZÀ-ÿ-]+( [a-zA-ZÀ-ÿ-]+)*$/)){ //on accepte aussi les espaces après un mot uniquement
        // console.log("ville rror");
        erreur_ville_compte.innerText = "La ville ne doit pas contenir de caractères spéciaux, ni de nombres, ni d'espace à la fin du nom."
        return false;
    }
    else if ((create_postal.value.length >= 5 && create_postal.value.length <= 5) === false) {
        erreur_postal_compte.innerText = "Le code postal doit avoir une longueur de 5."
        return false;
    } else if ((create_tel.value.length >= 10 && create_postal.value.length <= 10) === false){
        erreur_tel_compte.innerText = "Le numéro de téléphone doit avoir une longueur de 10."
        return false;
    } else if (pwdOne.value !== pwdTwo.value){
        erreur_pwd.innerText = "Le mot de passe et la confirmation doivent être identiques."
        return false;
    } else if (!pwdOne.value.match(/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[\W])([a-zA-Z0-9À-ÿ\W]+)$/)){
        erreur_pwd.innerText = "Utilisez au moins un chiffre, un caractère spécial (#?&!$€*@) et une lettre.";
        return false;
    }
    else {
        return true;
    }
}