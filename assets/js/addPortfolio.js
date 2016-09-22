$(document).ready(function() {
  addPortfolio();
});


function addPortfolio() {

  console.log("addPortfolio.js is loaded");

  $.ajax({ url: 'http://localhost/studip/plugins_packages/Universitaet%20Osnabrueck/EportfolioPlugin/controllers/createportfolio.php',
        type: 'POST',
        success: function() {
            console.log("ajax success");
        }
});

}
