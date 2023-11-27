let booleanFields = document.querySelectorAll('.form-switch');
let actionDeleteFields = document.querySelectorAll('.action-delete');
let tableauLines = document.querySelectorAll('tr'); //non utilisé, ignorer

function fixHttpBoolFields(){
    if(booleanFields !== null){
        booleanFields.forEach((bf) => {
            if(bf.children[0].dataset.toggleUrl.match('https') == null){
                bf.children[0].dataset.toggleUrl = bf.children[0].dataset.toggleUrl.replace('http','https');
            }
        })
    }
}

function fixHttpActionDeleteFields(){
    if(actionDeleteFields !== null){
        actionDeleteFields.forEach((adf) => {
            if(adf.attributes.formaction.value.match('https') == null){
                adf.attributes.formaction.value = adf.attributes.formaction.value.replace('http','https');
            }
        })
    }
}

// tableauLines.forEach((tb) => {
//     if(tb.querySelector('.form-check-input') !== null){
//         tb.querySelector('.form-check-input').addEventListener("change", () => {
//             if(!tb.querySelector('.form-check-input').parentElement.parentElement.parentElement.querySelector('.category_ea').children[0].innerText.match('Munitions')){
//                 alert('NO MA?');
//                 tb.querySelector('.form-check-input').checked = false;  
//             }
//             console.log('qfdfb');
//         })
//     }
// })

//UTILISE UNIQUEMENT SI LIEN EN HTTP et pas en HTTPS pour EA
fixHttpBoolFields();
fixHttpActionDeleteFields();
