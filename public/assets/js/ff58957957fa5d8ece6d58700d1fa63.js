let arrowsAll = document.querySelectorAll("#menu-two li i");
let divMarques = document.querySelectorAll(".menuPageSousCategories");
let rechercherMarques = document.getElementById('rechercherMarques');
let menuFiltreDesktop = document.querySelectorAll('.menuFiltreDesktop');
let datapart = "";
let isMenuFiltreDesktop = false;
// let lesCategories = {
//     categorie : [
//         {"nom":"Armes CAT. B", "HTML" :  
//         "<div>" +
//         "<h5>Les catégories B</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=2'><li class='textNoSelect'>Toutes les armes</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=2&famille%5B%5D=1'><li class='textNoSelect'>Pistolet</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=2&famille%5B%5D=2'><li class='textNoSelect'>Armes d'épaule</li></a>"
//         + "</div>"},

//         {"nom":"Armes CAT. C", "HTML" :  
//         "<div>" +
//         "<h5>Les catégories C</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Toutes les armes</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Armes d'épaule</li></a>"
//         + "<div>"},

//         {"nom":"Accessoires", "HTML" :  
//             "<div class='sous_categorie'>" +
//             "<h5>Pour les armes</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=6'><li class='textNoSelect'>Silencieux</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=25'><li class='textNoSelect'>Compensateurs</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Canons</li></a>" +             
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=17'><li class='textNoSelect'>Chargeurs</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=12'><li class='textNoSelect'>Crosses</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=30'><li class='textNoSelect'>Culasses</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=19'><li class='textNoSelect'>Détentes</li></a>" +
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Les accessoires</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6'><li class='textNoSelect'>Tous les accessoires</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=4'><li class='textNoSelect'>Kit nettoyage</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=26'><li class='textNoSelect'>Pièces détachées</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=8'><li class='textNoSelect'>Lampes</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=14'><li class='textNoSelect'>Montage</li></a>" +
//             "</div>"},

//             {"nom":"Equipements", "HTML" :  
//             "<div class='sous_categorie'>" +
//             "<h5>Protection</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=11'><li class='textNoSelect'>Gilets</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=16'><li class='textNoSelect'>Auditif</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=15'><li class='textNoSelect'>Oculaire</li></a>" +
//             "<a href='/nos-produits?famille%5B%5D=23'><li class='textNoSelect'>Plaque balistique</li></a>" +
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Les équipements</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=5'><li class='textNoSelect'>Tous les équipements</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=18'><li class='textNoSelect'>Lampes tactiques</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=9'><li class='textNoSelect'>Matraques</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=22'><li class='textNoSelect'>Transports</li></a>" +
//             "<a href='/nos-produits?famille%5B%5D=7'><li class='textNoSelect'>Sprays</li></a>" +            
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Optiques</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=13'><li class='textNoSelect'>Points rouge</li></a>" + 
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=29'><li class='textNoSelect'>Vision thermique</li></a>" + 
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=20'><li class='textNoSelect'>Vision nocturne</li></a>" + 
//             "</div>"
//             },

//             {"nom":"Munitions", "HTML" :  
//             "<div>" +
//             "<h5>Les munitions</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=11'><li class='textNoSelect'>Munitions CAT. B</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=12'><li class='textNoSelect'>Munitions CAT. C</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=13'><li class='textNoSelect'>Munitions CAT. D</li></a>"
//             + "</div>"},
//     ]
// }
// let lesCategories = {
//     categorie : [
//         {"nom":"Armes de chasse", "HTML" :  
//         "<div>" +
//         "<h5 class='sous_categorie'>Winchester</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=2'><li class='textNoSelect'>Fusils superposes</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=2&famille%5B%5D=1'><li class='textNoSelect'>Fusils semi-automatiques</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=2&famille%5B%5D=2'><li class='textNoSelect'>Fusils à pompe</li></a>"
//         + "</div>" +
//         "<div>" +
//         "<h5 class='sous_categorie'>Tikka</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=2'><li class='textNoSelect'>Carabines grande chasse</li></a>"
//         + "</div>" +
//         "<h5 class='sous_categorie'>Monocoup et petit calibre</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=2'><li class='textNoSelect'>Fusils lisses à 2 coups</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=2'><li class='textNoSelect'>Fusils lisses monocoup</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=2'><li class='textNoSelect'>Fusils silencieux</li></a>"
//         + "</div>" +
//         "<h5 class='sous_categorie'>Silencieux</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=2'><li class='textNoSelect'>Modérateurs de son chasse</li></a>"
//         + "</div>"},

//         {"nom":"Armes règlementées", "HTML" :  
//         "<div class='d-flex flex-column'><div>" +
//         "<h5>Armes de poing règlementées</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Chargeurs et accessoires</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Pistolets catégorie B</li></a>"
//         + "</div><div>" +
//         "<h5>Carabines semi-automatiques</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Carabines semi-automatiques</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Carabines semi-automatiques .22LR</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Chargeurs et accessoires</li></a>"
//         + "</div><div>" +
//         "<h5>Carabines à verrou</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Carabines à verrou B</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Carabines à verrou C</li></a>"+
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Chargeurs et accessoires</li></a>"
//         + "</div><div>" +
//         "<h5>Fusils à pompe ou semi-auto</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Fusils à pompe</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Fusils semi-automatique</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Chargeurs et accessoires</li></a>"
//         + "</div></div><div class='d-flex flex-column'><div>" +
//         "<h5>Armes de surplus</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Armes de poings</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Armes longues</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Chargeurs et accessoires</li></a>"
//         + "</div><div>" +
//         "<h5>Armes non létales</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Flashball</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Matraques</li></a>"
//         + "</div><div>" +
//         "<h5>Accessoires de tir</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Crosses et devants</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Silencieux et frein de bouche</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Chargettes</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Grips et poignées</li></a>"
//         + "</div><div>" +
//         "<h5>Holster et équipements</h5>" +
//         "<hr>" +
//         "<a href='/nos-produits?categories%5B%5D=1'><li class='textNoSelect'>Ceinturons et brelages</li></a>" +
//         "<a href='/nos-produits?categories%5B%5D=1&famille%5B%5D=3'><li class='textNoSelect'>Holsters pour armes</li></a>"
//         + "</div><div></div>"},

//         {"nom":"Armes de loisirs", "HTML" :  
//             "<div class='sous_categorie'>" +
//             "<h5>Carabines .22LR, .17MHR, 222 Rem</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=6'><li class='textNoSelect'>Carabines .22LR et .17HMR</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=25'><li class='textNoSelect'>Carabines 222 Rem</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Chargeurs et accessoires</li></a>" +             
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Carabines de survie</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6'><li class='textNoSelect'>Carabines pliantes</li></a>" +
//             "</div>"+
//             "<div class='sous_categorie'>" +
//             "<h5>Accessoires poudre noire</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=6'><li class='textNoSelect'>Poudres, poires et sels</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=25'><li class='textNoSelect'>Moules à balles</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Cheminées et clefs</li></a>" + 
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Visée et guidons</li></a>" +  
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Outillage</li></a>" +  
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Mallettes de tir</li></a>" +        
//             "</div>"+ 
//             "<div class='sous_categorie'>" +
//             "<h5>Armes de poing à air comprimé ou CO2</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=6'><li class='textNoSelect'>Pistolets CO2</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=25'><li class='textNoSelect'>Pistolets à air PCP</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Pistolets de tir à air comprimé</li></a>" + 
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Revolvers à CO2</li></a>" +  
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Chargeurs et accessoires</li></a>" +  
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Carabines à air comprimé ou CO2</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=6'><li class='textNoSelect'>Carabines de compétition</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=25'><li class='textNoSelect'>Carabines à air comprimé</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Carabines à air PCP</li></a>" + 
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Carabines à CO2</li></a>" +  
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=24'><li class='textNoSelect'>Accessoires</li></a>" +  
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Silencieux et pare-flammes</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=6&famille%5B%5D=6'><li class='textNoSelect'>Modérateurs de son</li></a>" +
//             "</div>"},

//             {"nom":"Equipements", "HTML" :  
//             "<div class='sous_categorie'>" +
//             "<h5>Protection</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=11'><li class='textNoSelect'>Gilets</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=16'><li class='textNoSelect'>Auditif</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=15'><li class='textNoSelect'>Oculaire</li></a>" +
//             "<a href='/nos-produits?famille%5B%5D=23'><li class='textNoSelect'>Plaque balistique</li></a>" +
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Les équipements</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=5'><li class='textNoSelect'>Tous les équipements</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=18'><li class='textNoSelect'>Lampes tactiques</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=9'><li class='textNoSelect'>Matraques</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=22'><li class='textNoSelect'>Transports</li></a>" +
//             "<a href='/nos-produits?famille%5B%5D=7'><li class='textNoSelect'>Sprays</li></a>" +            
//             "</div>" +
//             "<div class='sous_categorie'>" +
//             "<h5>Optiques</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=13'><li class='textNoSelect'>Points rouge</li></a>" + 
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=29'><li class='textNoSelect'>Vision thermique</li></a>" + 
//             "<a href='/nos-produits?categories%5B%5D=5&famille%5B%5D=20'><li class='textNoSelect'>Vision nocturne</li></a>" + 
//             "</div>"
//             },

//             {"nom":"Munitions", "HTML" :  
//             "<div>" +
//             "<h5>Les munitions</h5>" +
//             "<hr>" +
//             "<a href='/nos-produits?categories%5B%5D=11'><li class='textNoSelect'>Munitions CAT. B</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=12'><li class='textNoSelect'>Munitions CAT. C</li></a>" +
//             "<a href='/nos-produits?categories%5B%5D=13'><li class='textNoSelect'>Munitions CAT. D</li></a>"
//             + "</div>"},
//     ]
// }

//AFFICHER MENU CATEGORIES DEPUIS LE BOUTON "Nos produits" du header
function afficherMenuCategorie(el){
    el.children[0].classList.toggle("arrowspin");
    if(window.innerWidth <= 768){
        if(isMenuResponsive){
            document.querySelector(".navbarCustom-toggler").classList.toggle("spin");
            document.querySelector(".navbarCustom").classList.toggle("show_menu");
            isMenuResponsive = false;
        }
        menuCategorieMobile.classList.toggle("show");
    } else {
        menuCategorie.classList.toggle("show");
    }
}
function fermerMenuCategoriesMobile(){
    menuCategorieMobile.classList.remove("show");
}

// function afficherMenuCategorieParts(el,state){
//         target = 0;
//         if(window.innerWidth <= 768){
//             target = 1; //dernier elements = mobile
//         } else {
//             target = 0; //premier elements = non mobile
//         }
//         if(datapart == ""){ //quand au debut
//             divex[target].classList.remove("noshow");
//         }
//         if(datapart === el.dataset.catpart && divex[target].classList[divex[target].classList.length-1] !== "noshow"){ //si bouton pressé correspond à la précédente et il n'y a pas de classe noshow alors
//             divex[target].classList.add("noshow"); //on n'affiche plus
//             menuCategorie.classList.remove("show"); //pour version PC
//         } else {
//             divex[target].classList.remove("noshow"); //sinon on affiche
//             menuCategorie.classList.add("show"); //pour version PC
//         }
//         // divexSection[target].innerHTML = ;
//         for(let i = 0; i < divexSection[target].children.length; i++){
//             if(i !== state){ //check pour désafficher les categories qui ne correpondent pas
//                 divexSection[target].children[i].classList.add('noShowCategories');
//             }
//             divexSection[target].children[state].classList.remove('noShowCategories');
//         }
//         datapart = el.dataset.catpart; //attribution sauvegarde de la valeur du bouton pressé du divexSection
//     }

function afficherMenuCategorieParts(el,state){
    target = 0;
    if(window.innerWidth <= 768){
        target = 1; //dernier elements = mobile
    } else {
        target = 0; //premier elements = non mobile
    }
    if(datapart == ""){ //quand au debut
        divex[target].classList.remove("noshow");
    }
    if(datapart === el.dataset.catpart && divex[target].classList[divex[target].classList.length-1] !== "noshow"){ //si bouton pressé correspond à la précédente et il n'y a pas de classe noshow alors
        divex[target].classList.add("noshow"); //on n'affiche plus
        if(target === 0){
            menuCategorie.classList.remove("show"); //pour version PC
        }
        if(target === 1){
           menuCategorieMobile.classList.remove("show"); 
        }
    } else {
        divex[target].classList.remove("noshow"); //sinon on affiche
        if(target === 0){
            isMenuFiltreDesktop = true;
            menuCategorie.classList.add("show"); //pour version PC
        }
        if(target === 1){
            isMenuFiltreResponsive = true;
            menuCategorieMobile.classList.add("show"); 
        }
    }
    // divexSection[target].innerHTML = ;
    for(let i = 0; i < divexSection[target].children.length; i++){
        if(i !== state){ //check pour désafficher les categories qui ne correpondent pas
            divexSection[target].children[i].classList.add('noShowCategories');
        }
        divexSection[target].children[state].classList.remove('noShowCategories');
    }
    datapart = el.dataset.catpart; //attribution sauvegarde de la valeur du bouton pressé du divexSection
}

function fermerMenuCategoriesPartsMobile(){
    divex[1].classList.add("noshow"); //mobile
    setTimeout(() => { //pour pas avoir de conflits avec la fonction desafficherMenuResponsive()
        isMenuFiltreResponsive = false;
    }, 600);
}
function afficherSousMenu(el,enfant){ //enfant = numéro impaire qui correspond à un ul spécifié 
    el.children[0].classList.toggle("arrowspin");
    el.parentElement.children[enfant].children[0].classList.toggle("show"); //on va prendre le parent du el puis prendre le deuxième enfant qui est le ul
}

function rechercherLesMarques(obj,resultat){ //petite barre de recherche pour page marque
    // console.log(resultat);
    obj.forEach((o) => {
        if(!(o.querySelector('h5').innerText.match(resultat) || o.querySelector('h5').innerText.match(resultat[0].toUpperCase() + resultat.substring(1)) || o.querySelector('h5').innerText.match(resultat[0].toLowerCase() + resultat.substring(1)) || o.querySelector('h5').innerText.match(resultat.toUpperCase()))){
            o.classList.add("noshow");
        } else {
            if(o.classList.length > 0){
                o.classList.remove("noshow");
            }
        }
    })
}

if(rechercherMarques !== null){
    rechercherMarques.addEventListener("keyup", () => { 
        rechercherLesMarques(divMarques,rechercherMarques.value);
    });
}

function desafficherMenuCategorieParts(e){
    if(isMenuFiltreDesktop){
        if(!divex[0].contains(e.target) && !NAVBAR_SITE.contains(e.target) && !menuCategorie.contains(e.target)){ //si aucun des clics sont dans le panel menu filtre
            menuCategorie.classList.remove("show");
            divex[0].classList.add("noshow"); //on n'affiche plus
            isMenuFiltreDesktop = false;
        }
    }
}

document.addEventListener("click", (e) => {    
    desafficherMenuCategorieParts(e);
})

const lesMarques = document.querySelectorAll('#marques .form-check input');
const lesCalibres = document.querySelectorAll('#calibre .form-check input');
const lesCategoriesListe = document.querySelectorAll('#categories .form-check input');
const orderPriceFiltrer = document.getElementById('orderPriceFiltrer');
const objectBoxOne = document.getElementById('isOccasion');
const objectBoxTwo = document.getElementById('isPromo');
const objectBoxThree = document.getElementById('isFDO');

function panelFiltreActionJS(obj, string){
    obj.forEach((lm) => {
        lm.addEventListener("click", (e) => {
            let linkBase = "";
            // console.log(e.target.value);
            if(!window.location.href.match('submit=') && (!window.location.href.match('subCategories') && !window.location.href.match('famille') && !window.location.href.match('marques'))){
                linkBase = '/nos-produits?submit=';
            }
            if(!window.location.href.match(string + e.target.value)){ //si string no existo
                if(linkBase){
                    window.location.replace(linkBase + '&' + string + e.target.value);
                } else {
                    window.location.replace(window.location.href + '&' + string + e.target.value);
                }
            } else {
                window.location.replace(window.location.href.replace('&' + string + e.target.value, ''));
            }
    
        });
    })
}

function orderPrix(obj){
    let commit = "";
    let linkBaser = "";
    obj.addEventListener("change", () => {
        if(obj.value == "ASC"){
            commit = "ASC";
        }
        if(obj.value == "DESC"){
            commit = "DESC";
        }
        if(!window.location.href.match('submit=') && (!window.location.href.match('subCategories') && !window.location.href.match('famille') && !window.location.href.match('marques'))){
            linkBaser = '/nos-produits?submit=';
        }
        if(!window.location.href.match('orderPrices=')){ //si string no existo
            if(linkBaser){
                window.location.replace(linkBaser + '&' + 'orderPrices=' + commit);
            } else {
                window.location.replace(window.location.href + '&' + 'orderPrices=' + commit);
            }
        } else {
            if(commit == "ASC" && window.location.href.match('orderPrices=DESC')){ //si existo
                window.location.replace(window.location.href.replace('&' + 'orderPrices=DESC', '&' + 'orderPrices=' + commit));
            }
            if(commit == "DESC" && window.location.href.match('orderPrices=ASC')){
                window.location.replace(window.location.href.replace('&' + 'orderPrices=ASC', '&' + 'orderPrices=' + commit));
            }
        }
    });
}

function objectBoxSearchJS(bool, string){
    let linkBase = "";
    // let commit = 0;
    bool.addEventListener("click", (e) => {
        // if(bool.checked){
        //     commit = 1;
        // } else {
        //     commit = 0
        // }
        if(!window.location.href.match('submit=') && (!window.location.href.match('subCategories') && !window.location.href.match('famille') && !window.location.href.match('marques'))){
            linkBase = '/nos-produits?submit=';
        }
        if(!window.location.href.match(string + e.target.value)){ //si string no existo
            if(linkBase){
                window.location.replace(linkBase + '&' + string + e.target.value);
            } else {
                window.location.replace(window.location.href + '&' + string + e.target.value);
            }
        } else {
            window.location.replace(window.location.href.replace('&' + string + e.target.value, ''));
        }
    });
}
if(filtreparties !== null){
    panelFiltreActionJS(lesMarques, 'marques%5B%5D=');
    panelFiltreActionJS(lesCalibres, 'calibre%5B%5D=');
    panelFiltreActionJS(lesCategoriesListe, 'categories%5B%5D=');
    if(orderPriceFiltrer !== null){
        orderPrix(orderPriceFiltrer);
    }
    if(objectBoxOne !== null && objectBoxTwo !== null && objectBoxThree !== null){
        objectBoxSearchJS(objectBoxOne, 'isOccasion=');
        objectBoxSearchJS(objectBoxTwo, 'isPromo=');
        objectBoxSearchJS(objectBoxThree, 'isFDO=');
    }
}


// $('#marques .form-check input').each(function(){
//     $(this).click(function(e){
//         let marque = $(this).val();
//         console.log(marque);
//         $.ajax({
//             url: "/nos-produits",
//             type: "POST",
//             dataType: "text",
//             data: {
//                 "searchFiltre" : {
//                     "marques" : marque
//                 },
//             },
//             async: true,
//             success: function ()
//             { 
        
//             }
//         });
//     })
// });