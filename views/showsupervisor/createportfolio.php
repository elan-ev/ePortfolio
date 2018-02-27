<?= \Studip\Button::create(_("Vorlage verteilen"), 'newvorlage', array('onclick' => "exportPortfolio('$masterid')")) ?>

<input id='path' value=''/>
<fieldset>
<? foreach($semList as $sem):?>
    <?= \Studip\Button::create(_("Vorlage verteilen für " . $sem), '', array('onclick' => "importPortfolio('$sem')", 'id' => $sem)) ?><br/>


<? endforeach ?>
</fieldset>

<form data-dialog="size=auto;reload-on-close" action="<?= URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor/distributeportfolios/". $groupid . '/' . $masterid) ?>"
      method="post" enctype="multipart/form-data"
      <?= Request::isAjax() ? "data-dialog" : "" ?>>

<div data-dialog-button>
    <?= \Studip\Button::create(_("Abschließen"), 'finish', array("data-dialog"=>"")) ?>
</div>
</form>



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
      patharray = path.split(' ');
      path = patharray[patharray.length-1]
      $('#path').val(path);
    },
    error: function(data){
        alert('Beim Export ist ein Fehler aufgetreten: ' + data);
    }    
  });
}
  
function importPortfolio(target){
    urlimport = STUDIP.URLHelper.getURL('plugins.php/courseware/importportfolio', {cid: target}); //url import
    //$('#'+target).prop('disabled', true);
    $('#'+target).hide();
        $.ajax({
          type: "POST",
          url: urlimport,
          data: {
            target: target,
            path: $('#path').val(),
          },
          success: function(importData){
            console.log("###importData:");
            console.log(importData);
            $('#'+target).prop('disabled', true);
            //window.location = "";
          },
          error: function(data){
                alert('Beim Import ist ein Fehler aufgetreten: ' + data);
          }  
        });
}
  
</script>