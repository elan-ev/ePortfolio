
<div data-dialog-button>
    <?= \Studip\Button::create(_("Vorlage verteilen"), 'newvorlage', array('onclick' => "exportPortfolio('$masterid')", "data-dialog"=>"close")) ?>
</div>


<script>

function exportPortfolio(master){

  urlexport = STUDIP.URLHelper.getURL('plugins.php/courseware/exportportfolio', {cid: master}); //url export

  $.ajax({
    type: "GET",
    url: urlexport,
    success: function(exportData){
      var path = exportData; //export data
      console.log("###exportPath:");
      console.log(path);

      urlimport = STUDIP.URLHelper.getURL('plugins.php/courseware/importportfolio', {cid: 'e10c5a03a8248cbd14abab70f0655475'}); //url import

      targets = <?php echo json_encode($semList);?>;
      targets.forEach(function(target) {

        urlimport = STUDIP.URLHelper.getURL('plugins.php/courseware/importportfolio', {cid: target}); //url import

        $.ajax({
          type: "POST",
          url: urlimport,
          data: {
            // xml: xml,
            master: master,
            target: target,
            path: path,
          },
          success: function(importData){
            console.log("###importData:");
            console.log(importData);
            closeModal();
            //location.reload();
          }
        });
      });

    }
  });
}
</script>