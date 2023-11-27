let lesproduits = document.querySelectorAll(".produitbox");
let productitem = document.querySelectorAll(".product-item");
let productitem_titre_meilleur = document.querySelectorAll(".product-item h5");
let productitem_titre_page = document.querySelectorAll(".produit-item h4");
let productitem_subtitle_meilleur = document.querySelectorAll(".product-item span");
let productitem_subtitle_page = document.querySelectorAll(".produit-item span");
let productitem_subtitle_cart = document.querySelectorAll(".table_cart tbody tr td p");
let leng = 30;
let isMenuResponsive = false;
let isMenuFiltreResponsive = false;
let isPanelFiltre = false;
let produitpageprice = document.querySelector(".produit-page-price");
let produitpagecents = document.querySelector(".produit-page-centimes");
let productimage = document.querySelectorAll(".produit-item a img"); //pour page produits index
let imageproduit = document.getElementById('produit-photo'); //pour page show
let productitem_image = document.querySelectorAll('.produit-item img'); //pour page index
let lesimageproduits = document.querySelectorAll('.image-produit-aside');
const listeAssociationProduits = document.getElementById("listeAssociationProduits"); //pour page d'un seul produit

let carteFideletePfp = document.getElementById('carteFidelite_pfp');
let carteFideliteCreer = document.getElementById('panelCreationCarteFidelite');
let filtrecategoriesall = document.querySelectorAll('#categories option');
let filtremarquesall = document.querySelectorAll('#marques option');
let filtrePartieCalibres = document.querySelectorAll(".filtres #calibre > option");
let filtre_barrederecherche = document.querySelectorAll('.barderecherche');
let textarealimit = document.querySelector('.textarealimit');
let textarealimitdisplayer = document.getElementById('textarealimitdisplay');
let alertdisplay = document.querySelectorAll('.alertdisplay');

let maxprix = document.getElementById("maxprice");
let minprix = document.getElementById("minprice");
let maxprixMobile = document.getElementById("maxpriceMobile");
let minprixMobile = document.getElementById("minpriceMobile");

let displayminprix = document.getElementById("displayminprix");
let displaymaxprix = document.getElementById("displaymaxprix");

let searchbutton = document.querySelectorAll(".buttonrechercher");
let searchbutton_global = document.querySelectorAll(".barderecherche_filtrer");

let filtrebuttons = document.querySelectorAll(".btn-filtre");
let filtreparties = document.querySelectorAll(".partie-filtres .filtres");
let barreRechercheMarqueFiltre = document.getElementById("filtrer_marque_recherche");
let filtreparties_ck = document.querySelectorAll(".partie-filtres .form-check-input");
let filtrepartiesMarques = document.querySelectorAll(".filtres .menuMarques > .form-check");
let sectionPartieFiltreCalibre = document.getElementById("btn-filtre-calibres-uniq");
let filtreFamille = document.querySelectorAll(".familleFiltre > .form-check");
let stopCheckCalibre = false;
let checkCalibre = [];
let reset_filtre = document.getElementById("reset_filtre");
let arrow = document.querySelectorAll(".btn-filtre .arrow");
let arrowMobile = document.querySelectorAll(".btn-filtre-mobile .arrow");
let category_ea = '';
let accessoireLie_ea = document.querySelector('.accessoireLie_ea');

let categorylist = document.querySelector('.category_ea');
let produit_accessoireLieA = document.querySelector('#Produit_accessoireLieA');

let navbarToggler = document.querySelector('.navbar-toggler');
let navbarCollapse = document.getElementById('navbarCollapse');
let navbarCustom = document.querySelector(".navbarCustom");
let headerLogo = document.querySelector(".navbar-brand > img");
let navbarCustomHeaderSearch = document.querySelector("#navbarCustomHeaderSearch");
const NAVBAR_SITE = document.querySelector("header > #navbar_main");
let boutonpagesuivante = document.querySelector('#pagesuivanteproduits div'); 
let menuCategorie = document.querySelector(".rechercherparcategorie #menu-two");
let menuCategorieMobile = document.querySelector("#rechercheparcategorieMobile #menu-two");
let sectionPageSousCategorie = document.querySelectorAll(".sectionPageSousCategories > a");
let divex = document.querySelectorAll(".divex"); /* menu filtre catégories */
let divexSection = document.querySelectorAll(".divex > section > .contenu_sous_menu");
let section_overflow = document.querySelectorAll('.overflow-isbest');
let isDown = false;
var lien = new URL(window.location.toLocaleString());
/*Pour animation scroll automatique overflow*/
var animerUn, animerDeux, animerTrois;
var compterUn, compterDeux, compterTrois;
var tabAnimer = [animerUn, animerDeux, animerTrois];
var tabCompter = [compterUn, compterDeux, compterTrois];
var overflows = [
    [ document.getElementById("overflow-allisbest"), false], //[0] = section de l'overflow | [1] = état de l'animation (true = animer/false = pas animer)
    [ document.getElementById("overflow-allproduits"), false],
    [ document.getElementById("overflow-allproduits-promo"), false],
];
/*     */
let scroll_isbest_gauche = document.querySelectorAll('.scroll-isbest-gauche');
let scroll_isbest_droite = document.querySelectorAll('.scroll-isbest-droite');

let panier = document.querySelectorAll(".ajoutPanier");


function styleprice(prc){ //formatage du prix, centime en petit
    if(prc != null){
        prc.setAttribute("style","font-size: 0.5em; position: relative; bottom: 12px;");
    }
}
function listederoulantereset(cat){ //reset à chaque fois la liste déroulante
    if(cat != undefined){ //cat.multiple = false;
        cat.forEach((opt) => {
            opt.selected = "";
            if(opt.classList.length > 0){
                opt.classList.remove("noshow");
            }
        })
    }
}
function limittextarea(box){ //limite du caractère du message de la page contacte
    if(box != undefined){
        //box.maxLength = 500;
        box.addEventListener('keyup',(event) => {
            document.getElementById('textcompteur').innerText = box.textLength; //afichage combien de caractère sur le message
            if(box.textLength >= 500){ //limite à 500 caractères
                textarealimitdisplayer.style.color = "red";
            } else {
                textarealimitdisplayer.style.color = "";
            }
        })
    }
}
function removealertdisplay(message, timer){ //enlève le message d'un envoi ou d'erreur d'envoi de message
    message.forEach((msg) => {
        if(msg != undefined){
            setTimeout(() => {
                msg.style.opacity = 0;
                setTimeout(() => {
                    msg.remove();
                },600);
            },timer);
        }
    });
}

function searchicone(button){ //bouton icone recherche pour barre de recherche (fontawesome on pouvait)
    if(button != undefined){
        button.innerHTML = "<svg fill='#FFFFFF' xmlns='http://www.w3.org/2000/svg'  viewBox='0 0 30 30' width='27px' height='27px'><path d='M 13 3 C 7.4889971 3 3 7.4889971 3 13 C 3 18.511003 7.4889971 23 13 23 C 15.396508 23 17.597385 22.148986 19.322266 20.736328 L 25.292969 26.707031 A 1.0001 1.0001 0 1 0 26.707031 25.292969 L 20.736328 19.322266 C 22.148986 17.597385 23 15.396508 23 13 C 23 7.4889971 18.511003 3 13 3 z M 13 5 C 17.430123 5 21 8.5698774 21 13 C 21 17.430123 17.430123 21 13 21 C 8.5698774 21 5 17.430123 5 13 C 5 8.5698774 8.5698774 5 13 5 z'/></svg>" ;
    }
}

function buttonfiltresaction(btn, filtres, arrow){
    if(btn != undefined && filtres != undefined){
        for(let i = 0; i < filtres.length; i++){
            btn[i].addEventListener("click", () =>{
                filtres[i].classList.toggle("displayfiltre");
                arrow[i].classList.toggle("arrownspin");
                btn[i].classList.toggle("closed");
            })
        }
    }
}

// function armePickCalibre(el){
//     console.log(el.value);
//     if(el.value === 1 || el.value === 2 || el.value === 3){ //si arme cat c ou b ou d alors
//         sectionPartieFiltreCalibre.classList.remove("noshow");
//     } else {
//         sectionPartieFiltreCalibre.classList.add("noshow");
//     }
// }

function rechercherObjDepuisFiltre(obj,resultat){ //petite barre de recherche pour le panel à filtre (rechercher une marque par exemple)
    // console.log(resultat);
    obj.forEach((o) => {
        if(!(o.innerText.match(resultat))){
            o.classList.add("noshow");
        } else {
            if(o.classList.length > 0){
                o.classList.remove("noshow");
            }
            // console.log(o.innerText);
        }
    })
}
function photosAsideVisionner(img, imageaside){ //multiphotos pour page d'un seul produit
    if(img != undefined){
        let originalimg = img.src;
        if(imageaside != undefined){
            imageaside.forEach((imag) => {
                //console.log(imag.src);
                imag.addEventListener("mouseover",() => {
                    img.src = imag.src;
                })
                imag.addEventListener("mouseout",() => {
                    img.src = originalimg;
                })
            })
        }
    }
}

function checkAccessoireLiaison(categ,inputaccessoire,accessesoireLier){ //INIT au début check
    if(categ != undefined && inputaccessoire != undefined && accessesoireLier != undefined){
        categ = document.querySelector('#Produit_category-ts-control div');
        if (categ.dataset.value == 6){ 
            inputaccessoire.style.display = "block";
            accessesoireLier.disabled = false;
        } else {
            inputaccessoire.style.display = "none";
            accessesoireLier.disabled = true;
        } 
    }
}

function categorieAccessoireLiaison(categorylist,categ,inputaccessoire,accessesoireLier){ //pour easyadmin quand on selectionne accessoire en tant que catégorie
    if(categ != undefined && inputaccessoire != undefined && categorylist != undefined && accessesoireLier != undefined){
        categorylist.addEventListener("change",() => {
            categ = document.querySelector('#Produit_category-ts-control div'); //récupérer maj données de categorie
            if (categ.dataset.value == 6){ //si accessoire
                inputaccessoire.style.display = "block";
                accessesoireLier.disabled = false;
            } else {
                inputaccessoire.style.display = "none";
                accessesoireLier.disabled = true;
            }
        })
    }
}


function dfffff(produits,btn){
    if(leng > produits.length) { //pas de else car la comparaison des opérateurs n'est pas inversable
        btn.remove(); //on enlève ce bouton car limite atteinte ou même si length est déjà supérieur aux resultats affichés
    }
}

function afficherplus(produits, boutonpage){ //petit bouton en bas de chaque page des produits, pour en afficher plus
    if(boutonpage != undefined){
        // for(i = 0; leng > i; i++){ //on affiche les 30 premiers | INIT
        //     if(produits[i] != undefined){
        //         produits[i].classList.remove('noshow');
        //     }
        // }
        dfffff(produits,boutonpage);
        boutonpage.addEventListener("click",() => {
            if(produits.length >= leng){
                leng = leng + 30; //on affiche 30 par 30 de produits
                for(i = leng - 30; leng > i; i++){
                    if(i <= produits.length){
                        produits[i-1].classList.remove('noshow');
                    }
                } 
            }
            dfffff(produits,boutonpage);
        })
    }
}

/*******************/

function animationImageProduitIndex(images){ //pour faire effet survol image d'un produit dans la page des produits
    images.forEach((im) => {
        im.addEventListener("mouseover", () => {
            im.parentElement.children[0].style.opacity = 0.8;
        })
        im.addEventListener("mouseout",() => {
            im.parentElement.children[0].style.opacity = 0;
        })
    })
}
function limiteCaractereFormatage(produit,nb,limitcarac){ //Pour pas encombrer l'affichage du titre produit, par exemple limiter la taille d'un titre très très long
    if(produit != undefined ){
        produit.forEach((a) => { //on prend chaque produit disponible en DOM 
            if(a.innerText.split(' ').length >= nb && a.innerText.length >= limitcarac){ //si texte du produit long alors
            a.innerText = a.innerText.split(' ').slice(0,nb).join(' ') + "...";
           }
        }) 
        
    }
}
function resizeTextSiDeborde(element,nb,newsize){ //texte, taille police, taille de police à réduire
    let font_size = nb;
    element.forEach((a) => { //tenter de faire resizer de font par rapport à une longueur de texte
        a.style.fontSize = font_size + "rem"; //ou sinon on peut pas lire le fontSize
        if(/*a.clientHeight >= 50 ||*/ a.innerText.length >= 40){ 
        a.style.fontSize = (font_size - newsize) + "rem";
        }
    })
}
function resizeAllTextProduit(win){ //redimensionner les polices des produits
    if(win.innerWidth > 415){
        resizeTextSiDeborde(productitem_titre_page,1.0,0.25);
        if(win.innerWidth >= 990 && win.innerWidth < 768){
            resizeTextSiDeborde(productitem_titre_meilleur,0.9,0.1);  
        }
        if(win.innerWidth > 990){
            resizeTextSiDeborde(productitem_titre_page,1.0,0.2);
        } 
    } else { //si inférieur à 415px
        resizeTextSiDeborde(productitem_titre_page,0.9,0.2);
        resizeTextSiDeborde(productitem_titre_meilleur,1,0.2);
    }
}

function afficherMenuResponsiveTransition(win, navbarCustm){
    if(navbarCustm !== undefined && navbarCustm !== null){
        if(win.innerWidth < 768){
            navbarCustm.classList.add("transition_resp");  //effet de déplacement
        } else {
            navbarCustm.classList.remove("transition_resp"); //effet de déplacement
        }
    }
}
function afficherMenuResponsive(){
    if(!isMenuResponsive){
        setTimeout(() => {
            isMenuResponsive = true;
        },600); //delais pour pas que desafficherMenuResponsive() fasse des conflits
    } else {
        isMenuResponsive = false;
    }
    document.querySelector(".navbarCustom-toggler").classList.toggle("spin");
    document.querySelector(".navbarCustom").classList.toggle("show_menu");
    document.querySelectorAll(".grande_section_recherche").forEach((panelfiltre) => {
        if(panelfiltre !== null){
            panelfiltre.classList.remove("show_recherche"); //forcer à enlever la classe
            menuCategorieMobile.classList.remove("show"); //forcer à enlever la classe
        }
    })

}
function desafficherMenuResponsive(e){
    if(document.querySelector(".navbarCustom").classList.contains("show_menu")){
        if(isMenuResponsive && !isMenuFiltreResponsive){
            //console.log('1')
            if(!navbarCollapse.contains(e.target)){
                //console.log('2')
                document.querySelector(".navbarCustom-toggler").classList.remove("spin");
                document.querySelector(".navbarCustom").classList.remove("show_menu");
                isMenuResponsive = false;
            }
        }
    }
}
function setLeftValue(displayminprix,minimum,maxx){ //pour le filtre prix
    min = parseInt(minimum.min);
    max = parseInt(maxx.max);

    minimum.value = Math.min(parseInt(minimum.value), parseInt(maxx.value) - 150);
    prixMaxMinAffiche(displayminprix, minimum); 
// var percent = ((minimum.value - min) / (max - min)) * 100;

}

function setRightValue(displaymaxprix,maximum,minn){ //pour le filtre prix
    min = parseInt(minn.min);
    max = parseInt(maximum.max);

    maximum.value = Math.max(parseInt(maximum.value), parseInt(minn.value) + 150);
    prixMaxMinAffiche(displaymaxprix, maximum); 
// var percent = ((maximum.value - min) / (max - min)) * 100;
}
function resetPrixFiltre(displaymaxprix,maxi,displayminprix,mini){
    mini.value = Math.max(parseInt(mini.min));
    maxi.value = Math.max(parseInt(maxi.max));
    prixMaxMinAffiche(displaymaxprix, maxi); 
    prixMaxMinAffiche(displayminprix, mini); 
}
function prixMaxMinAffiche(text,valeur){ //pour le filtre prix
    text.innerHTML = valeur.value + "€";
}
function activerAnimationOverflowProduits(state, section_overflow, r){ // r = id du section_overflow en paramètre
    if(state){
        tabCompter[r] = setTimeout(() => {
            clearInterval(tabCompter[r]);
            animerOverflowProduits(85,section_overflow,state,r);
        },8000);
    } else {
        clearInterval(tabCompter[r]);
        animerOverflowProduits(85,section_overflow,state,r);
    }
}

function animerOverflowProduits(temps,prod,state,r){ //state = si animation
    if(state){
        tabAnimer[r] = setInterval(() => {
            prod.scrollLeft = prod.scrollLeft + 5;
        },temps);
    } else {
        clearInterval(tabAnimer[r]);
        // console.log("clear");
    }
}

function overflowButtons(droite, gauche, overflows, r){
        droite.addEventListener("click", (e) => {
            e.preventDefault();
            overflows[1] = false;
            activerAnimationOverflowProduits(overflows[1], overflows[0], r);
            overflows[0].scrollLeft = overflows[0].scrollLeft + 300;
            // console.log(overflows[0]);
        });
        gauche.addEventListener("click", (e) => {
            e.preventDefault();
            overflows[1] = false;
            activerAnimationOverflowProduits(overflows[1], overflows[0], r);
            overflows[0].scrollLeft = overflows[0].scrollLeft - 300;
        });
}
function filtrePrixRendu(minprix,maxprix,displaymaxprix, displayminprix){
    if(minprix !== undefined && maxprix !== undefined && displayminprix !== undefined && displaymaxprix !== undefined){
        setLeftValue(displayminprix, minprix,maxprix);
        setRightValue(displaymaxprix, maxprix,minprix);
        prixMaxMinAffiche(displayminprix, minprix);
        prixMaxMinAffiche(displaymaxprix, maxprix);
    }

    minprix.addEventListener("change",() => {
       setLeftValue(displayminprix,minprix,maxprix);
    });
    maxprix.addEventListener("change", () => {
        setRightValue(displaymaxprix,maxprix,minprix);
    });

}
function afficherLesFiltres(){ //afficher grand panel pour les filtres
    if(navbarCustomHeaderSearch.classList.length >= 2){ //si plus de deux classe style ([]: showdef et pageProduit)
        navbarCustomHeaderSearch.classList.remove('showdef');
        isPanelFiltre = true; //override pour apres le retourner en false
    } else {
        document.querySelectorAll(".grande_section_recherche").forEach((panelfiltre) => {
            panelfiltre.classList.toggle("show_recherche"); //afficher ou non le panel à filtre
        })
    }
    if(!isPanelFiltre){
        isPanelFiltre = true;
    } else {
        isPanelFiltre = false;
    }
}
function fermerLesFiltres(){ 
    if(navbarCustomHeaderSearch.classList.length >= 2){
        navbarCustomHeaderSearch.classList.remove('showdef');
    } else {
        document.querySelectorAll(".grande_section_recherche").forEach((panelfiltre) => {
            panelfiltre.classList.remove("show_recherche"); //afficher ou non le panel à filtre
        })
    }
    isPanelFiltre = false;
}
function resetFiltre(reset_filtre, ckbox){ //reinitialisation des valeurs du filtre
    if(reset_filtre != undefined){
        reset_filtre.addEventListener("click", () => { //si bouton reset filtre cliqué

            listederoulantereset(filtrecategoriesall);
            listederoulantereset(filtremarquesall);
            listederoulantereset(filtreFamille);
            listederoulantereset(filtrePartieCalibres);
            resetPrixFiltre(displaymaxprix,maxprix,displayminprix,minprix);
            filtre_barrederecherche.forEach((br) => { //vider la barre de recherche
                br.value = '';
            })
            ckbox.forEach((re) => {
                re.checked = false;
            })
            window.history.replaceState({}, "", window.location.href.split('?')[0]); //keep only before ? to lien reset si sauvegardé
        })
    }
}

function scrollNavbarAffichage(window, navbar){ //pour mobile quand on scroll en bas, le navbar horizontal disparait
    if(navbar !== null){
        let scrollValue = 150;
        if(window.innerWidth <= 768){
            scrollValue = 150;
        } else {
            scrollValue = 250;
        }
        if(!isMenuResponsive){
            if (window.scrollY >= scrollValue){ //la valeur du scroll, 1 scroll = 100
                if (window.oldScroll < window.scrollY){ 
                    navbar.style.transform = "translateY(-" +  1.2 * navbar.clientHeight +"px)";

                    if( document.querySelectorAll(".grande_section_recherche")[0] !== undefined){ //si enfant de grande_section_recherche existe
                        document.querySelectorAll(".grande_section_recherche")[0].classList.add('anim');
                    }
                } else {
                    navbar.style.transform = "translateY(0)";
                    if( document.querySelectorAll(".grande_section_recherche")[0] !== undefined){
                        document.querySelectorAll(".grande_section_recherche")[0].classList.remove('anim');
                    }
                }
            }
        // console.log(window.scrollY);
        window.oldScroll = window.scrollY;
        } else {
            navbar.style.transform = "translateY(0)"; //override
        }
    }
}
function afficherUploadImageBeta(event){ //fonctionne uniquement pour carte fidelite, affichage en rendu image profile fidelite
    div = carteFideletePfp;
    div.src = URL.createObjectURL(event.target.files[0]);
    div.onload = function() {
        URL.revokeObjectURL(div.src) // free memory
    }
}
// function afficherCreationCarteFidelite(but){ //A EFFACE
//     carteFideliteCreer.style.display = "block";
//     but.remove();
// }
function kuk(){
    headerLogo.src = "/assets/image/tactical-pro.jpg";
    document.querySelector(".navbar-brand-mobile > img").src = "/assets/image/tactical-pro.jpg";
}
//INITIALISATION
function init(){

    styleprice(produitpagecents); 
    resetFiltre(reset_filtre, filtreparties_ck);
    photosAsideVisionner(imageproduit, lesimageproduits);
    if(barreRechercheMarqueFiltre !== null){
        barreRechercheMarqueFiltre.addEventListener("keyup", () => { //version desktop > 768px
            rechercherObjDepuisFiltre(filtrepartiesMarques, barreRechercheMarqueFiltre.value);
        });
    }
    if(listeAssociationProduits !== null){
        listeAssociationProduits.addEventListener("change", () => { //pour liste déroulante produit association dans page d'un seul produit
            window.location.href = listeAssociationProduits.value;
        });
    }
    limittextarea(textarealimit);
    removealertdisplay(alertdisplay,6000);
    searchbutton.forEach((sb) => {
        searchicone(sb);
    });
    window.addEventListener("scroll",(e) => {
        scrollNavbarAffichage(window, NAVBAR_SITE);
    })

    if(section_overflow !== undefined){
        let x, avancer, startX, scrollLeft;

        for(let r = 0; r < overflows.length; r++){
            if(overflows[r][0] !== null){
                activerAnimationOverflowProduits(overflows[r][1], overflows[r][0],r); //INIT
                //pc
                overflows[r][0].addEventListener('mousedown',(e) => {
                    e.preventDefault();
                    overflows[r][1] = false;
                    activerAnimationOverflowProduits(overflows[r][1], overflows[r][0],r);
                });

                overflows[r][0].addEventListener('mouseup',(e) => {
                    e.preventDefault();
                    overflows[r][1] = true;
                    activerAnimationOverflowProduits(overflows[r][1], overflows[r][0],r);
                });
                //mobile
                overflows[r][0].addEventListener('touchstart',(e) => {
                    overflows[r][1] = false;
                    activerAnimationOverflowProduits(overflows[r][1], overflows[r][0],r);
                });
                overflows[r][0].addEventListener('touchend',(e) => {

                    overflows[r][1] = true;
                    activerAnimationOverflowProduits(overflows[r][1], overflows[r][0],r);
                });
                overflowButtons(scroll_isbest_droite[r], scroll_isbest_gauche[r], overflows[r], r); //pour les deux arrows en sous

            }
        }

        section_overflow.forEach((ov) => { 
            // pour pc pour scroll dans la section overflow avec le clic de la souris en maintien
            ov.addEventListener('mousedown', (e) => { //quand la souris/tactile (mousedown = touchdown) est cliquée/maintenue
                isDown = true;
                e.preventDefault();
                startX = e.pageX - ov.offsetLeft; //position de la souris en X moins la valeur du scroll offsetLeft du overflow
                scrollLeft = ov.scrollLeft;

            })
            ov.addEventListener('mouseleave', (e) => {
                isDown = false;
            })
            ov.addEventListener('mouseup', (e) => {
                isDown = false;
            })
            ov.addEventListener('mousemove', (e) => { //quand la souris bouge
                if(isDown){ 
                    x = e.pageX - ov.offsetLeft;
                    avancer = (x - startX) * 2;
                    ov.scrollLeft = scrollLeft - avancer;
                }
            })
        });  
    }  
    afficherMenuResponsiveTransition(window, navbarCustom);
    resizeAllTextProduit(window); //avant le chargement
    window.addEventListener("resize",() =>{ //pendant le redimensionnement
        resizeAllTextProduit(this);
        afficherMenuResponsiveTransition(this, navbarCustom);
    })
    limiteCaractereFormatage(productitem_subtitle_cart,10,40);

    afficherplus(lesproduits,boutonpagesuivante);
    buttonfiltresaction(filtrebuttons, filtreparties, arrow);
    animationImageProduitIndex(productimage);
    window.addEventListener("load",() =>{

        checkAccessoireLiaison(category_ea,accessoireLie_ea,produit_accessoireLieA);
        categorieAccessoireLiaison(categorylist,category_ea,accessoireLie_ea,produit_accessoireLieA);
        if(navbarCollapse !== null){
            document.addEventListener("click", (e) => {
                if(isMenuResponsive){
                    desafficherMenuResponsive(e);
                }
            });
        }
    })
    filtrePrixRendu(minprix,maxprix,displaymaxprix, displayminprix);
    sectionPageSousCategorie.forEach((sc) => {
        sc.addEventListener('mouseover', () => {
            sc.children[0].children[0].classList.add('hoverJs');
        })
        sc.addEventListener('mouseout', () => {
            sc.children[0].children[0].classList.remove('hoverJs');
        })
    })
    if(navbarCustomHeaderSearch.classList.length >= 1 && window.innerWidth <= 768){ //si class et ecran en taille mobile alors on supprime la classe directement pour page des produits
        navbarCustomHeaderSearch.classList.remove('showdef');
    } 
}

init(); //init