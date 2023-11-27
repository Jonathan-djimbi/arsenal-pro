let inp = document.getElementById("inputPassword");
let eye = document.querySelector(".sectionAfficherLoginPass > p > i");
let hide = document.getElementById("showHidePassword");
let rf = document.getElementById("refund_prix");
let toggle = true;
let formPA = document.getElementById('formProAssocSectionCompte');
let formFDO = document.getElementById('fdoSection');
const proAssoc = document.getElementById('proAssoc');
const fdo = document.getElementById('fdo');


if(hide !== null && hide !== undefined){
    hide.addEventListener("click", () => {
        if(toggle){
            inp.type = "text";
            eye.classList.remove("fa-eye");
            eye.classList.add("fa-eye-slash");    
            toggle = false;
        } else {
            inp.type = "password";
            eye.classList.add("fa-eye");
            eye.classList.remove("fa-eye-slash"); 
            toggle = true;
        }
    })
}

if(rf !== undefined && rf !== null){
    rf.type = "number";
}

if(proAssoc !== undefined && formPA !== null && proAssoc !== null){
    proAssoc.addEventListener("change", () => {
        if(proAssoc.checked){
            formPA.classList.remove('noshow');
        } else {
            formPA.classList.add('noshow');
        }
    })
}
if(fdo !== undefined && formFDO !== null && fdo !== null){
    fdo.addEventListener("change", () => {
        if(fdo.checked){
            formFDO.classList.remove('noshow');
        } else {
            formFDO.classList.add('noshow');
        }
    })
}

