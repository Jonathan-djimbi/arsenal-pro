let compteurCart = document.querySelectorAll('.compteurQuantiteCart');

function inputQuantite(val,pid){
    compteurQuantite = parseInt(val); //string en int
    if (val !== ""){ //si inférieur à la quantite OU si champs vide
        console.log(val, pid);
        window.location.replace('/cart/add/' + pid + '/' + compteurQuantite);
    } 
    // console.log(compteurQuantite >= quantiteMax);
}

for(let i = 0; i < compteurCart.length; i++){
    compteurCart[i].addEventListener('change', () => {
        inputQuantite(compteurCart[i].value, compteurCart[i].dataset.pid);
    })
}

