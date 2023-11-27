let venteFlashTimer = document.getElementById('venteFlashTimer');
let venteFlashCompteurGlobal = document.querySelectorAll('.venteFlashCompteurGlobal');
let boutonAcheterPageProduit = document.querySelector('.btn-acheter-page-produit');
var jours, heures, minutes, secondes, distance;
var futurTempsProduit, futurTempsAll;

function mettreUnZero(n){
    if(n < 10){ //si inférieur à 10, ajouter un 0 comme 09, 08, 07, 06 secondes...
        return "0" + n;
    } else {
        return n;
    }
}
function joursCheck(n){
    if(n === 0){ //si jour inférieur à 1 jour (en sous de 24 heures)
        return 1;
    } else {
        return n * 24;
    }
}
function btnUp(btn){ //OUTDATED
    btn.innerText = "VENTE TERMINÉE";
    btn.href = "javascript:void(0)";
    btn.classList.add("btn-secondary");
    btn.classList.remove("btn-primary");
}

//compteur
if(venteFlashTimer !== null){ //pour page d'un seul produit
    futurTempsProduit = new Date(venteFlashTimer.dataset.ptime).getTime();
    var vf = setInterval(function() {
            var now = new Date().getTime();
            distance = futurTempsProduit - now; //difference entre deux dates

            jours = Math.floor(distance / (1000 * 60 * 60 * 24));
            heures = Math.floor((distance % (1000 * 60 * 60 * ((joursCheck(jours) * 24))) / (1000 * 60 * 60))); 
            minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            secondes = Math.floor((distance % (1000 * 60)) / 1000);
            // Afficher à l'aide du HTML le compte à rebours
            venteFlashTimer.innerHTML = "<p>VENTES FLASH ! <br>"  + mettreUnZero(heures) + " : " + mettreUnZero(minutes) + " : " + mettreUnZero(secondes) + "</p>";

            if(distance < 0) {
                clearInterval(vf);
                venteFlashTimer.innerHTML = "<p>VENTES FLASH TERMINÉE</p>";
                // btnUp(boutonAcheterPageProduit);
            }
    }, 1000);
}

if(venteFlashCompteurGlobal){ //pour pages plusieurs produits /nos-vente-flash
    var vfg = setInterval(() => {
        venteFlashCompteurGlobal.forEach((vfglo) => {
            futurTempsAll = new Date(vfglo.dataset.ptime).getTime();
            var now = new Date().getTime();
            distance = futurTempsAll - now; //difference entre deux dates
            jours = Math.floor(distance / (1000 * 60 * 60 * 24));
            heures = Math.floor((distance % (1000 * 60 * 60 * ((joursCheck(jours) * 24))) / (1000 * 60 * 60))); 
            minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            secondes = Math.floor((distance % (1000 * 60)) / 1000);
    
            // Afficher à l'aide du HTML le compte à rebours
            vfglo.innerHTML = ""  + mettreUnZero(heures) + " : " + mettreUnZero(minutes) + " : " + mettreUnZero(secondes) + "";

            if(distance < 0) {
                vfglo.innerHTML = "VENTES FLASH TERMINÉE";
            }
        })
    }, 1000)
}