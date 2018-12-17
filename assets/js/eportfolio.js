

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
    vars[key] = value;
    });
    return vars;
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
