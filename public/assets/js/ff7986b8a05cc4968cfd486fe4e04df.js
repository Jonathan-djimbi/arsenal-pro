let compteurQuantite = 0;
let enleverQuantiteBourse = document.getElementById('enleverQuantiteBourse');
let ajouterQuantiteBourse = document.getElementById('ajouterQuantiteBourse');
let compteurQuantiteBourse = document.getElementById('compteurQuantiteBourse');
let btnPanier = document.querySelector('.btn-acheter-page-produit');
var quantiteMax = btnPanier.dataset.qtm;
let lengthArray = [];
 
function updateR(state){
    switch(state){
        case 0 :
            btnPanier.href = "javascript:void(0)";
            break;
        case 1 :
            btnPanier.href = "/produit-en-bourse/cart/add/" + btnPanier.dataset.id + "/quantite=" + compteurQuantite;
            break;
        default :
            break;
    }
}
function ajouterQuantite(){
    compteurQuantite++;
    compteurQuantiteBourse.value = compteurQuantite;
    if(compteurQuantite >= quantiteMax){
        updateR(1); //uplink
        btnPanier.classList.remove('noshow');
    }
}
function removeQuantite(){
    if(compteurQuantite == 0){
        $compteurQuantite = 0;
    } else {
        compteurQuantite--;
    }
    compteurQuantiteBourse.value = compteurQuantite;
    if(compteurQuantite < quantiteMax){
        updateR(0); //reset
        btnPanier.classList.add('noshow');
    } else {
        updateR(1); //uplink
    }
}
function inputQuantite(val){
    compteurQuantite = parseInt(val.value); //string en int
    if (compteurQuantite < quantiteMax || val.value == ""){ //si inférieur à la quantite OU si champs vide
        updateR(0);
        btnPanier.classList.add('noshow');
    }
    else {
        updateR(1); //reset
        btnPanier.classList.remove('noshow');
    } 
    // console.log(compteurQuantite >= quantiteMax);
}
enleverQuantiteBourse.addEventListener('click', () => removeQuantite());
ajouterQuantiteBourse.addEventListener('click', () => ajouterQuantite());

compteurQuantiteBourse.addEventListener('keyup',() => { //maj par ecriture chiffre
    inputQuantite(compteurQuantiteBourse);
})
compteurQuantiteBourse.addEventListener('change',() => { //maj par arrows input
    inputQuantite(compteurQuantiteBourse);
})


// for(let ix = 0; ix < prixEvolution.length; ix++){ //compte chaque itinération/achat du tableau prix bourse
//     lengthArray.push(ix);
// }

// let graphique = new Chart("charteBourse", {
//     type: "line",
//     data: {
//       labels: quantiteNombre, 
//       label: "Quantité(s) commandée(s)",
//       datasets: [{
//         fill: false,
//         lineTension: 0,
//         label: "Prix en temps réel (euros)",
//         backgroundColor: "rgba(0,0,255,0.6)",
//         borderColor: "rgba(0,0,255,0.6)",
//         data: prixEvolution,
//         pointRadius: 3,
//         tension: 0.1,
//         // segment: {
//         //     borderColor: (ctx) => (ctx.p0.parsed.y < ctx.p1.parsed.y ? '#cc5762c7' : '#28a745') //couleurs graphe
//         //   }
//       }]
//     },
//     options: {
//         legend: {display: true},
//         scales: {},
//         plugins: {
//             annotation: {
//               annotations: {
//                 line: {
//                   type: 'line',
//                   yMin: prixEvolution[0],
//                   yMax: prixEvolution[0],
//                   borderWidth: 2,
//                   borderColor: 'green'
//                 }
//               }
//             }
//           }
//       },
//   });

let onePrice = [];
for(let i = 0; i < prixEvolution.length; i++){
    onePrice.push(prixEvolution[0]);
}
var options = {
    series: [{
        name: "Evolution du prix en euros",
        type : 'area',
        data: prixEvolution
    },
    {
        name: 'Prix de base en euros',
        type: 'line',
        data: onePrice
    }],
    chart: {
        width: '100%',
        height: '400',
        type: 'line',
        zoom: {
            enabled: false
        },
    },
    colors: ['#008FFB', '#00E396'],
    fill: {
        type: ['gradient', 'solid'], //1er data = gradient, 2eme data = solid
        gradient: {
        shade: "light",
        type: "vertical",
        shadeIntensity: 0,
        opacityFrom: 1,
        opacityTo: 0.5
        }
    },
    dataLabels: {
        enabled: false
    },
        stroke: {
        curve: 'straight'
    },

    // subtitle: {
    // text: 'Price Movements',
    // align: 'left'
    // },
    labels: quantiteNombre,
    xaxis: {
        type: 'numeric',
    },
    yaxis: {
        opposite: true,
        labels: {
            formatter: function(val) {
              return val.toFixed(2) + "€";
            }
          },
    },
    legend: {
        horizontalAlign: 'left'
    },
    // responsive: [
    //     {
    //       breakpoint: 1000,
    //       options: {
    //         plotOptions: {
    //           area: {
    //             horizontal: false
    //           }
    //         },
    //         legend: {
    //           position: "bottom"
    //         }
    //       }
    //     }
    //   ]
    methods: {
    //   updateWidth() {
    // //   console.log(document.getElementById("container").offsetWidth);
    //     let w = document.querySelector(".produit-section-bourse");
    //     if(w.offsetWidth > 900) w.style.width = "900px"
    //     else w.style.width = "350px"
    //   }
    },
};

var chart = new ApexCharts(document.querySelector("#charteBourse"), options);
chart.render();