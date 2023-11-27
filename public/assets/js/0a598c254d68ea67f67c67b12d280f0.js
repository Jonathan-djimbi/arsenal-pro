let formControl = document.querySelectorAll('.form-control');
let inputFiles = ["Produit_illustration_file","Produit_illustrationun_file","Produit_illustrationdeux_file","Produit_illustrationtrois_file","Produit_illustrationquatre_file"]; //ID champs définis du easyadmin
let inputPromo = document.getElementById('CodePromo_pourcentage');
let inputPromoMontant = document.getElementById("CodePromo_montantRemise");
let inputPrixPromo = document.getElementById('RemiseGroupe_remise');
const inputMaxAmountPromo = document.getElementById("CodePromo_maxAmount");

inputFiles.forEach((i) => {
    if(document.getElementById(i) !== undefined && document.getElementById(i) !== null){
        document.getElementById(i).addEventListener("change", () => {
            if((document.getElementById(i).files[0].size / (1024*1024)).toFixed(2) > 4){ //dashboard produit 4MB max
                checker = false;
                window.alert("Le fichier est trop volumineux (" + (document.getElementById(i).files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 4 MB.");
                // i.classList.add("form-input-border-error");

                //on vide le champs dans le easyadmin
                document.getElementById(i).value = null;
                document.getElementById(i).labels[0].innerText = 'Veuillez réinsérer une image (4 MB MAX)';
            } 
        })
    }
})

function codePromoPourcentageLimiteEA(codepromo_field){ //pour easyadmin, limite % insertion code promo
    if(codepromo_field != undefined){
        let value = codepromo_field.value;
        codepromo_field.type = "number"; //de base c'était en text, on ne pouvait pas utiliser les attributs max et min 
        codepromo_field.step = 0.01;
        codepromo_field.max = 100; //max 100%
        codepromo_field.min = 0.1; //minimum 0.1%

        codepromo_field.value = value.replace(',','.');
    }
}
function codePromoMontantRemiseLimiteEA(codepromo_field){
    if(codepromo_field != undefined){
        let value = codepromo_field.value;
        codepromo_field.type = "number"; //de base c'était en text, on ne pouvait pas utiliser les attributs max et min 
        codepromo_field.step = 0.01;
        codepromo_field.min = 1; //minimum 1 euros
        codepromo_field.value = value.replace(',','.');
    }
}

function codePromoSansConflits(){ //pas de conflits entre pourcentage et remise euros formulaire Dashboard
    if(inputPromoMontant.value !== ""){
        inputPromo.disabled = true;
        inputPromoMontant.disabled = false;
        inputMaxAmountPromo.required = true;
        if(inputPromoMontant.value != ""){
            inputMaxAmountPromo.labels[0].innerText = 'Valeur minimum autorisée panier : ' + (parseInt(inputPromoMontant.value) + 2) + '€ conseillée';
        }
    } else {
        inputPromo.disabled = false;
        inputMaxAmountPromo.required = false;
    }
    if(inputPromo.value !== ""){
        inputPromo.disabled = false;
        inputPromoMontant.disabled = true; 
        inputMaxAmountPromo.required = false;
    } else {
        inputPromoMontant.disabled = false; 
        inputMaxAmountPromo.required = true;
        if(inputPromoMontant.value != ""){
            inputMaxAmountPromo.labels[0].innerText = 'Valeur minimum autorisée panier : ' + (parseInt(inputPromoMontant.value) + 2) + '€ conseillée';
        }
    }
}
if(inputPromo != undefined && inputPromoMontant != undefined){
    codePromoSansConflits() //exécuté au début une foit en init

    inputPromoMontant.addEventListener("change", () => {
        codePromoSansConflits();
    });

    inputPromo.addEventListener("change", () => {
        codePromoSansConflits();
    });
}

codePromoMontantRemiseLimiteEA(inputPromoMontant);
codePromoPourcentageLimiteEA(inputPromo); //code promo
codePromoPourcentageLimiteEA(inputPrixPromo); //remise groupe