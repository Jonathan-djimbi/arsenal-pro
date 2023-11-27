//check front-end
const formControl = document.querySelectorAll('.form-control');
const lienDepotVenteForm = document.getElementById('lienDepotVenteForm');
const depotVenteBtnUn = document.getElementById('depotVenteBtnUn');
const depotVenteBtnDeux = document.getElementById('depotVenteBtnDeux');
let depotVenteTextUn = document.getElementById('depotVenteTextUn');
let depotVenteTextDeux = document.getElementById('depotVenteTextDeux');
let depot_nbTotalArme = document.getElementById('nbTotalArme');
let depot_nbArmeEpaule = document.getElementById('nbArmeEpaule');
let checker = true;
let photoUn = document.getElementById('photoUn');
let photoDeux = document.getElementById('photoDeux');
let photoTrois = document.getElementById('photoTrois');
let photoQuatre = document.getElementById('photoQuatre');
let inputDate = document.getElementById('naissance');
let numPhone = document.getElementById('phoneDepot');
const erreurPhotoUn = document.getElementById('erreur_photoUn');
const erreurPhotoDeux = document.getElementById('erreur_photoDeux');
const erreurPhotoTrois = document.getElementById('erreur_photoTrois');
const erreurPhotoQuatre = document.getElementById('erreur_photoQuatre');
const erreur_Date = document.getElementById('erreur_Date');
const erreur_Phone = document.getElementById('erreur_Phone');
let dateNow = bornDatum = new Date();

function ageDiff(a,b) {
    let age = a.getFullYear() - b.getFullYear(); //en annee
    //console.log(age);
    if(age < 18){ //si en sous de 18 ans
        return false;
    } else {
        return true;
    }
}

lienDepotVenteForm.addEventListener('click', () => {
    document.getElementById('form').scrollIntoView();
})
if(depotVenteBtnUn !== null && depotVenteBtnDeux !== null){
    depotVenteBtnUn.addEventListener('click', () => {
        depotVenteTextUn.classList.toggle("noshow");
    })

    depotVenteBtnDeux.addEventListener('click', () => {
        depotVenteTextDeux.classList.toggle("noshow");
    })
}


function verifImages(){ //taille + format
    checker = true;
    bornDatum = new Date(inputDate.value);
    //clear les modifs d'erreurs
    erreurPhotoUn.innerText = "";
    erreurPhotoDeux.innerText = "";
    erreurPhotoTrois.innerText = "";
    erreurPhotoQuatre.innerText = "";
    erreur_Date.innerText = "";
    erreur_Phone.innerText = "";
    formControl.forEach((fm) => {
        fm.classList.remove("form-input-border-error");
    })
    //
    if(!ageDiff(dateNow,bornDatum)){ //comparer age
        checker = false;
        erreur_Date.innerText = "Vous devez au moins avoir 18 ans pour déposer des armes.";
        inputDate.classList.add("form-input-border-error");
    }
    if((numPhone.value.length > 10 || numPhone.value.length < 9 )){
        checker = false;
        erreur_Phone.innerText = "Le numéro doit uniquement contenir des chiffres et doit avoir entre 9 à 10 chiffres.";
        numPhone.classList.add("form-input-border-error");
    }
    if(photoUn.files.length > 0){ //si fichier existe
        if((photoUn.files[0].size / (1024*1024)).toFixed(2) > 6){ //si supérieure à 6MB
            checker = false;
            erreurPhotoUn.innerText = "L'image est trop volumineuse (" + (photoUn.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 6 MB."
            photoUn.classList.add("form-input-border-error");
        } 
        if((!photoUn.files[0].type.match('image/') || photoUn.files[0].type.match('image/') == null)){
            checker = false;
            erreurPhotoUn.innerText = "Le fichier n'est pas une image (Mauvais format : ." + photoUn.files[0].name.split('.').at(-1)  + "). Veuillez insérer une image (JPG, JPEG)."
            photoUn.classList.add("form-input-border-error");
        } 
    }
    if(photoDeux.files.length > 0){ //si fichier existe
        if((photoDeux.files[0].size / (1024*1024)).toFixed(2) > 6){ 
            checker = false;
            erreurPhotoDeux.innerText = "L'image est trop volumineuse (" + (photoDeux.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 6 MB."
            photoDeux.classList.add("form-input-border-error");
        } 
        if((!photoDeux.files[0].type.match('image/') || photoDeux.files[0].type.match('image/') == null)){
            checker = false;
            erreurPhotoDeux.innerText = "Le fichier n'est pas une image (Mauvais format : ." + photoDeux.files[0].name.split('.').at(-1)  + "). Veuillez insérer une image (JPG, JPEG)."
            photoDeux.classList.add("form-input-border-error");
        } 
    }
    if(photoTrois.files.length > 0){ //si fichier existe
        if((photoTrois.files[0].size / (1024*1024)).toFixed(2) > 6){ 
            checker = false;
            erreurPhotoTrois.innerText = "L'image est trop volumineuse (" + (photoTrois.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 6 MB."
            photoTrois.classList.add("form-input-border-error");
        } 
        if((!photoTrois.files[0].type.match('image/') || photoTrois.files[0].type.match('image/') == null)){
            checker = false;
            erreurPhotoTrois.innerText = "Le fichier n'est pas une image (Mauvais format : ." + photoTrois.files[0].name.split('.').at(-1)  + "). Veuillez insérer une image (JPG, JPEG)."
            photoTrois.classList.add("form-input-border-error");
        } 
    }
    if(photoQuatre.files.length > 0){ //si fichier existe
        if((photoQuatre.files[0].size / (1024*1024)).toFixed(2) > 6){ 
            checker = false;
            erreurPhotoQuatre.innerText = "L'image est trop volumineuse (" + (photoQuatre.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 6 MB."
            photoQuatre.classList.add("form-input-border-error");
        } 
        if((!photoQuatre.files[0].type.match('image/') || photoQuatre.files[0].type.match('image/') == null)){
            checker = false;
            erreurPhotoQuatre.innerText = "Le fichier n'est pas une image (Mauvais format : ." + photoQuatre.files[0].name.split('.').at(-1)  + "). Veuillez insérer une image (JPG, JPEG)."
            photoQuatre.classList.add("form-input-border-error");
        } 
    }
    if(!checker){
        document.getElementById('form').scrollIntoView();
    }
    return checker;
}