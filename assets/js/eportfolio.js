function createNewPortfolio() {
  $('#createForm').submit(function(e){

    var nameNewCreatePortfolio;
    var url = "/studip/plugins.php/eportfolioplugin/create";
    var idBannerSuccess = 'createPortfolioName';
    var classBannerSuccess = 'createPortfolioBanner';
    var idBannerAlert = '#createBannerAlert';

    //check everthing is filled out
    var empty = $(this).parent().find("input").filter(function() {
        return this.value === "";
    });

    if (empty.length) {
      $(idBannerAlert).css('display', 'block');
    } else {
      $.ajax({
        type: "POST",
        url: url,
        data: $("#createForm").serialize(),
        success: function(data) {
          nameNewCreatePortfolio = $( "#PortfolioName" ).val();
          $('#myModal').modal('hide');
           $('#createForm')[0].reset();
          updater();
          showBanner(nameNewCreatePortfolio, idBannerSuccess , classBannerSuccess);
        }
      });
    }

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });
}

// function updatePortfolioTable() {
//   $.ajax({
//     type: "POST",
//     url: "/studip/plugins.php/eportfolioplugin/updateportfolios",
//     dataType: "json",
//     data: {},
//     success: function(data) {
//       var i = data["counter"];
//
//       $.each(data, function(k, v){
//
//         var name = v['name'];
//         var beschreibung = v['beschreibung'];
//         var seminar_id = v['seminar_id'];
//
//         $('.portfolioOverview').append("<tr class='insert_tr'><td><a href='/studip/plugins.php/eportfolioplugin/eportfolioplugin?cid="+seminar_id+"'>"+name+"</a></td><td> "+beschreibung+" </td><td><i class='fa fa-minus-circle' aria-hidden='true'></i>  Keine</td></tr>");
//
//       });
//
//       PortfolioHeadline(i);
//       updateLabelPortfolios(i);
//     }
//   });
// }

// function updateAccessTable(){
//   $.ajax({
//     type: "POST",
//     url: "/studip/plugins.php/eportfolioplugin/updateaccess",
//     dataType: "json",
//     data: {},
//     success: function(data) {
//       var i = data["counter"];
//
//       $.each(data, function(k, v){
//
//         var name = v['name'];
//         var beschreibung = v['beschreibung'];
//         var seminar_id = v['seminar_id'];
//         var ownerName = v['ownerName'];
//
//         $('.viewportfolioOverview').append("<tr class='insert_tr'><td><a href='/studip/plugins.php/eportfolioplugin/eportfolioplugin?cid="+seminar_id+"''>"+name+"</a></td><td> "+beschreibung+" </td><td>"+ownerName+"</td></tr>");
//       });
//
//       updateLabelAccess(i);
//     }
//   });
// }

function deleteOldTableRows(){
  $('.insert_tr').each(function(){
    $(this).remove();
  });
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
    vars[key] = value;
    });
    return vars;
}

function showBanner(e, id, c) {
  var hideAfter = 6000;
  var animation = "slow";
  var idName = '#' + id;
  var className = '.' + c;

  $(idName).text(e);
  $(className).css('display', 'block');
  setTimeout(function() {$(className).fadeOut(animation);}, hideAfter);
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

function deletePortfolio() {
  var href = "/studip/plugins.php/eportfolioplugin/show";
  var url = "/studip/plugins.php/eportfolioplugin/settings?cid="+cid;
  $.ajax({
    type: "POST",
    url: url,
    data: {
      'deletePortfolio':'1',
    },
    success: function(data) {
      $('#deletebtn').remove();
      $('#deleteText').remove();
      $('.deleteSuccess').css('display', 'block');
      setTimeout(function(){
        window.location.href = '/studip/plugins.php/eportfolioplugin/show';
      }, 1500);
    }
  });
}

function setAccess(id, viewerId){
  var url = "/studip/plugins.php/eportfolioplugin/settings?cid="+cid;
  $.ajax({
    type: "POST",
    url: url,
    data: {
      'setAccess':'1',
      'block_id': id,
      'viewer_id': viewerId,
    },
    success: function(data) {
      alert(data);
    }
  });
}

function checkIcon(viewerId, id) {
  var className = $('#icon-'+viewerId+'-'+id).attr('class');
  if (className == "glyphicon glyphicon-remove") {
    $('#icon-'+viewerId+'-'+id).removeClass("glyphicon-remove");
    $('#icon-'+viewerId+'-'+id).addClass("glyphicon-ok");
  } else if (className == "glyphicon glyphicon-ok") {
    $('#icon-'+viewerId+'-'+id).removeClass("glyphicon-ok");
    $('#icon-'+viewerId+'-'+id).addClass("glyphicon-remove");
  }
}

function setSupervisor(id){
  var url = "/studip/plugins.php/eportfolioplugin/settings?cid="+cid;

  $.ajax({
    type: "POST",
    url: url,
    data: {
      'setSupervisor': 1,
      "supervisorId": id,
    },
    success: function(data) {
    }
  });
}

function setViewer(id){
  var url = "/studip/plugins.php/eportfolioplugin/settings?cid="+cid;

  $.ajax({
    type: "POST",
    url: url,
    data: {
      'setViewer': 1,
      "viewerId": id,
    },
    success: function(data) {
      alert(data);
    }
  });
}
