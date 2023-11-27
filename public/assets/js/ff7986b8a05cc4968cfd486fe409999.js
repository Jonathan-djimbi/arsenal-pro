let dates = [];
let prixCa = [];
let ratioVentesValues = document.getElementById("camembertRatioVentes");
let ratioUserCommandesValues = document.getElementById("camembertRatioUserCommandes");
let ratioUserDocumentsValues = document.getElementById("camembertRatioUserDocuments");
prixEvolution.forEach((e) => {
    dates.push(e.date);
    prixCa.push(e.prix);
})

let graphique = new Chart("charteCA", {
    type: "line",
    data: {
      labels: dates,
      datasets: [{
        fill: false,
        lineTension: 0,
        label: "Prix en euros (€)",
        backgroundColor: "rgba(0,0,255,1.0)",
        borderColor: "rgba(0,0,255,0.4)",
        data: prixCa,
        // segment: {
        //     borderColor: (ctx) => (ctx.p0.parsed.y < ctx.p1.parsed.y ? '#cc5762c7' : '#28a745') //couleurs graphe
        //   }
      }]
    },
    options: {
        legend: {display: true},
        scales: {}
      }
  });

  function pieChart(id,labelOne,labelTwo,dataOne,dataTwo,colorOne,colorTwo){
    return new Chart(id, {
      // draw line chart
        type: "pie",
        // pie chart data
        data: {
          labels: [
            labelOne,
            labelTwo,
          ],
          datasets: [{
            label: false,
            data: [dataOne, dataTwo],
            backgroundColor: [
              'rgb(27,188,107)',
              'rgb(181,181,181)',
            ],
            hoverOffset: 4
          }],
      },
    });
  }
  let ventesRatio = pieChart("camembertRatioVentes","Ventes effectuées","Abandons",ratioVentesValues.dataset.success,ratioVentesValues.dataset.total,'rgb(27,188,107)','rgb(181,181,181)');
  let ventesRatioUser = pieChart("camembertRatioUserCommandes","Ayant commandé en %","N'ayant pas commandé en %",ratioUserCommandesValues.dataset.ratio,((100) - ratioUserCommandesValues.dataset.ratio),'rgb(27,188,107)','rgb(181,181,181)');
  let documentsRatioUser = pieChart("camembertRatioUserDocuments","Ayant déposé leurs documents en %","N'ayant pas déposé leurs documents en %", ratioUserDocumentsValues.dataset.ratio,((100) - ratioUserDocumentsValues.dataset.ratio),'rgb(27,188,107)','rgb(181,181,181)')