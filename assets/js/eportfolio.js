

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


function setSupervisor(id){
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings', {cid: cid});

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
  console.log("set viewer");
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings', {cid: cid});

  $.ajax({
    type: "POST",
    url: url,
    data: {
      'setViewer': 1,
      "viewerId": id,
    },
    success: function(data) {
      location.reload();
    }
  });
}
