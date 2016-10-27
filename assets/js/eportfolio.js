function createNewPortfolio() {
  $('#createForm').submit(function(e){
    var url = "/studip/plugins.php/eportfolioplugin/create";
    nameNewCreatePortfolio = $( "#PortfolioName" ).val();

    $.ajax({
      type: "POST",
      url: url,
      data: $("#createForm").serialize(),
      success: function(data) {
        $('#myModal').modal('hide');
        updater();
        showBanner(nameNewCreatePortfolio);
      }
    });

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });
}

function updatePortfolioTable() {
  $.ajax({
    type: "POST",
    url: "/studip/plugins.php/eportfolioplugin/updateportfolios",
    dataType: "json",
    data: {},
    success: function(data) {
      var i = data["counter"];

      $.each(data, function(k, v){

        var name = v['name'];
        var beschreibung = v['beschreibung'];
        var seminar_id = v['seminar_id'];

        $('.portfolioOverview').append("<tr class='insert_tr'><td><a href='/studip/plugins.php/eportfolioplugin/eportfolioplugin?cid="+seminar_id+"'>"+name+"</a></td><td> "+beschreibung+" </td><td><i class='fa fa-minus-circle' aria-hidden='true'></i>  Keine</td></tr>");

      });

      PortfolioHeadline(i);
      updateLabelPortfolios(i);
    }
  });
}

function updateAccessTable(){
  $.ajax({
    type: "POST",
    url: "/studip/plugins.php/eportfolioplugin/updateaccess",
    dataType: "json",
    data: {},
    success: function(data) {
      var i = data["counter"];

      $.each(data, function(k, v){

        var name = v['name'];
        var beschreibung = v['beschreibung'];
        var seminar_id = v['seminar_id'];
        var ownerName = v['ownerName'];

        $('.viewportfolioOverview').append("<tr class='insert_tr'><td><a href='/studip/plugins.php/eportfolioplugin/eportfolioplugin?cid="+seminar_id+"''>"+name+"</a></td><td> "+beschreibung+" </td><td>"+ownerName+"</td></tr>");
      });

      updateLabelAccess(i);
    }
  });
}

function deleteOldTableRows(){
  $('.insert_tr').each(function(){
    $(this).remove();
  });
}

function PortfolioHeadline(i) {
  var one = "Mein Portfolio";
  var two = "Meine Portfolios"

  if (i <= 1) {
    $('#headline_uebersicht').text('Mein Portfolio');
  } else {
    $('#headline_uebersicht').text('Meine Portfolios');
  }
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
    vars[key] = value;
    });
    return vars;
}

function showBanner(e) {
  var hideAfter = 6000;
  var animation = "slow";

  $('#createPortfolioName').text(e);
  $('.createPortfolioBanner').css('display', 'block');
  setTimeout(function() {$('.createPortfolioBanner').fadeOut(animation);}, hideAfter);
}

function updateLabelPortfolios(e) {
  if (e == null) {
    e = 0;
  }

  $('#labelMyPortfolio').text(e);
}

function updateLabelAccess(e) {
  if (e == null) {
    e = 0;
  }

  $('#labelAccess').text(e);
}
