let requiredCSIA = document.getElementById("compte_documents_numero_sea");
let checkCSIA = document.getElementById("noNumeroSIA");
let justDocsCC = document.querySelector(".justificatifDomicileDocument");
let adresseCNI = document.getElementById("adresseCNI");
let affichageDocument = document.getElementById('affichageDocument');

let fichierLicence = document.getElementById('compte_documents_licenceTirId');
let fichierCNI = document.getElementById('compte_documents_cartId');
let fichierCertMed = document.getElementById('compte_documents_certificatMedicalId');
let fichierPolice = document.getElementById('compte_documents_cartPoliceId');
let dateLicence = document.getElementById('compte_documents_licenceTirIdDate');
let dateCNI = document.getElementById('compte_documents_cartIdDate');
let dateCertMed = document.getElementById('compte_documents_certificatMedicalIdDate');
// let datePolice = document.getElementById('compte_documents_cartPoliceIdDate');
let jusDom = document.getElementById('compte_documents_justificatifDomicile');
let formControl = document.querySelectorAll('.form-control');

const erreur_fichierLicence = document.getElementById('erreur_fichierLicence');
const erreur_fichierCNI = document.getElementById('erreur_fichierCNI');
const erreur_fichierCertMed = document.getElementById('erreur_fichierCertMed');
const erreur_fichierJust = document.getElementById('erreur_fichierJust');
const erreur_fichierPolice = document.getElementById('erreur_fichierPolice');
const erreurDat = document.getElementById('topNotifDocuments');
const lesUsers = document.querySelectorAll('.lesUsers');
const lesUsersCol = document.querySelectorAll('.lesUsers > option');
const userRecherche = document.getElementById('rechercherUser');
let docAffiche = false;
let checker = true;
const dateNow = formatDate(new Date());

function formatDate(date) { //format pour YYYY-DD-MM
    var d = new Date(date),
        mois = '' + (d.getMonth() + 1),
        jour = '' + d.getDate(),
        annee = d.getFullYear();

    if (mois.length < 2) 
        mois = '0' + mois;
    if (jour.length < 2) 
        jour = '0' + jour;

    return [annee, mois, jour].join('-');
}

function clientNoNum(el, state){
    if(state === 0){
        if(el.checked && requiredCSIA.value == ""){ //si checké et champs numéro SIA vide
            requiredCSIA.required = false;
        } else {
            el.checked = false;
            requiredCSIA.required = true;
        }
    }
    if(state === 1){
        el.required = true;
        checkCSIA.checked = false;
    }
}
function verifJustDocs(state){ //front-end check no reload
    if(state === 0){ //submit
        checker = true;
        erreur_fichierLicence.innerText = "";
        erreur_fichierCNI.innerText = "";
        erreur_fichierCertMed.innerText = "";
        erreur_fichierJust.innerText = "";
        erreurDat.innerText = "";
        if(erreur_fichierPolice !== null){
            erreur_fichierPolice.innerText = "";
        }
        erreurDat.classList.remove("text-danger");
        formControl.forEach((fm) => {
            fm.classList.remove("form-input-border-error");
        })
        if(adresseCNI !== null){ //si checkbox cni disponible
            if(adresseCNI.checked == false){
                justDocsCC.classList.remove("noshow");
                if(document.getElementById("compte_documents_justificatifDomicile").value !== ""){
                    checker = true;
                } else {
                    checker = false;
                }
            } 
            if(adresseCNI.checked == true){
                checker = true;
            }
        }
        //check fichier taille
        if((fichierCNI.files[0].size / (1024*1024)).toFixed(2) > 4){
            checker = false;
            erreur_fichierCNI.innerText = "Le fichier est trop volumineux (" + (fichierCNI.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 4 MB."
            fichierCNI.classList.add("form-input-border-error");
        } 
        if (fichierLicence.files.length > 0){ //si fichier existe
            if((fichierLicence.files[0].size / (1024*1024)).toFixed(2) > 4){
                checker = false;
                erreur_fichierLicence.innerText = "Le fichier est trop volumineux (" + (fichierLicence.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 4 MB."
                fichierLicence.classList.add("form-input-border-error");
            } 
        }
    
        if (fichierCertMed.files.length > 0){ //si fichier existe
            if((fichierCertMed.files[0].size / (1024*1024)).toFixed(2) > 4){
                checker = false;
                erreur_fichierCertMed.innerText = "Le fichier est trop volumineux (" + (fichierCertMed.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 4 MB."
                fichierCertMed.classList.add("form-input-border-error");
            } 
        }
        if(fichierPolice !== undefined && fichierPolice !== null){
            if(fichierPolice.files.length > 0){ //si fichier existe
                if((fichierPolice.files[0].size / (1024*1024)).toFixed(2) > 4){
                    checker = false;
                    erreur_fichierPolice.innerText = "Le fichier est trop volumineux (" + (fichierPolice.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 4 MB."
                    fichierPolice.classList.add("form-input-border-error");
                } 
            }
        }

        if(jusDom.files.length > 0){
            if((jusDom.files[0].size / (1024*1024)).toFixed(2) > 4){
                checker = false;
                erreur_fichierJust.innerText = "Le fichier est trop volumineux (" + (jusDom.files[0].size / (1024*1024)).toFixed(2) + " MB). Sa taille ne doit pas dépasser 4 MB."
                jusDom.classList.add("form-input-border-error");
            }
        }

         //check fichier type/extension
         if((!fichierCNI.files[0].type.match('image/') || fichierCNI.files[0].type.match('image/') == null)){
            checker = false;
            erreur_fichierCNI.innerText = "Le fichier n'est pas une image (Mauvais format : ." + fichierCNI.files[0].name.split('.').at(-1)  + "). Veuillez insérer la CNI en tant qu'image (PNG, JPG, JPEG)."
            fichierCNI.classList.add("form-input-border-error");
        } 
        if (fichierLicence.files.length > 0){ //si fichier existe
            if(!fichierLicence.files[0].type.match('image/') || fichierLicence.files[0].type.match('image/') == null){
                checker = false;
                erreur_fichierLicence.innerText = "Le fichier n'est pas une image (Mauvais format : ." + fichierLicence.files[0].name.split('.').at(-1)  + "). Veuillez insérer la licence de tir en tant qu'image (PNG, JPG, JPEG)."
                fichierLicence.classList.add("form-input-border-error");
            } 
        }
    
        if (fichierCertMed.files.length > 0){ //si fichier existe
            if(!fichierCertMed.files[0].type.match('image/') || fichierCertMed.files[0].type.match('image/') == null){
                checker = false;
                erreur_fichierCertMed.innerText = "Le fichier n'est pas une image (Mauvais format : ." + fichierCertMed.files[0].name.split('.').at(-1)  + "). Veuillez insérer le certificat en tant qu'image (PNG, JPG, JPEG)."
                fichierCertMed.classList.add("form-input-border-error");
            } 
        }
        if(fichierPolice !== undefined && fichierPolice !== null){
            if(fichierPolice.files.length > 0){ //si fichier existe
                if(!fichierPolice.files[0].type.match('image/') || fichierPolice.files[0].type.match('image/') == null){
                    checker = false;
                    erreur_fichierPolice.innerText = "Le fichier n'est pas une image (Mauvais format : ." + fichierCertMed.files[0].name.split('.').at(-1)  + "). Veuillez insérer la carte police en tant qu'image (PNG, JPG, JPEG)."
                    fichierPolice.classList.add("form-input-border-error");
                } 
            }
        }
        if(jusDom.files.length > 0){
            if(!jusDom.files[0].type.match('image/') || jusDom.files[0].type.match('image/') == null){
                checker = false;
                erreur_fichierJust.innerText = "Le fichier n'est pas une image (Mauvais format : ." + jusDom.files[0].name.split('.').at(-1)  + "). Veuillez insérer le document en tant qu'image (PNG, JPG, JPEG)."
                jusDom.classList.add("form-input-border-error");
            }
        }
        
        //check datum
        if(dateCNI.value <= dateNow){
            checker = false;
            erreurDat.innerText = "Votre CNI n'est plus valable. Il a expiré. Veuillez s'il vous plaît mettre le document à jour."
            erreurDat.classList.add("text-danger");
            dateCNI.classList.add("form-input-border-error");
        } 
        
        if(dateLicence.value !== ""){
            if(dateLicence.value <= dateNow){
                checker = false;
                erreurDat.innerText = "Votre licence de tir n'est plus valable. Il a expiré. Veuillez s'il vous plaît mettre le document à jour."
                erreurDat.classList.add("text-danger");
                dateLicence.classList.add("form-input-border-error");
            }

        } 

        if(dateCertMed.value !== ""){
            if(dateCertMed.value <= dateNow){
                checker = false;
                erreurDat.innerText = "Votre certificat médical n'est plus valable. Il a expiré. Veuillez s'il vous plaît mettre le document à jour."
                erreurDat.classList.add("text-danger");
                dateCertMed.classList.add("form-input-border-error");
            }
        }  
        
        if(!checker){
            if(document.querySelector('.banniere_show') !== null){
                document.querySelector('.banniere_show').scrollIntoView();
            }
        }
        return checker;
    }
    if(state === 1){ //ckboxClick
        if(adresseCNI.checked){
            justDocsCC.classList.add("noshow");
            document.getElementById("compte_documents_justificatifDomicile").value = "";
        } else {
            justDocsCC.classList.remove("noshow");
        }
    }
}
// Fonction pour afficher l'image dynamiquement
function affichePreviewClient(input, previewElementId) {
    var previewElement = document.getElementById(previewElementId);
    previewElement.innerHTML = ''; // Efface le contenu précédent de l'élément de prévisualisation (en cas d'annulation)

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            // Créez un élément d'image
            var image = document.createElement('img');
            image.src = e.target.result;
            image.style.maxWidth = '15vh';
            image.classList.add('img-thumbnail', 'mt-2', 'd-block', 'mx-auto');

            previewElement.appendChild(image);
        };

        reader.readAsDataURL(input.files[0]);
    }
}



function afficheDocumentsClient(el){
    if(affichageDocument !== null){
        if(!docAffiche){
            affichageDocument.children[1].innerHTML = "<img class='d-block mx-auto' src='" +  el.dataset.docid + "' alt='mes-documents-arsenal-pro'/><br/><a href='" + el.dataset.docid + "' download>Télécharger le document</a>";
            affichageDocument.classList.remove('noshow');
            docAffiche = true;
        } else {
            affichageDocument.classList.add('noshow');
            affichageDocument.children[1].innerHTML = "";
            docAffiche = false;
        }
    }
}

function fermerPanelDocuments(){
    affichageDocument.classList.add('noshow');
    affichageDocument.children[1].innerHTML = "";
    docAffiche = false;
}
function searchUser(userRecherche, lesUsers){
    lesUsers.forEach((user) => {
        if(!(user.innerText.match(userRecherche.value)) ){
            user.classList.add("noshow");
        } else {
            if(user.classList.length > 0){
                user.classList.remove("noshow");
            }
            // console.log(o.innerText);
        }
    })
}
if(lesUsers !== null && userRecherche !== null){
    userRecherche.addEventListener("keyup", () => {
        searchUser(userRecherche, lesUsers);
    })
}

if(lesUsersCol !== null && userRecherche !== null){
    userRecherche.addEventListener("keyup", () => {
        searchUser(userRecherche, lesUsersCol);
    })
}
