<?= \Studip\Button::create(_("Vorlage verteilen"), 'newvorlage', array('onclick' => "exportPortfolio('$masterid')", 'id' => 'export')) ?>

<input id='path' type="hidden" value=''/>
<fieldset id="userportfolios" style="display:none">
    <h1>Verteilen an: </h1>
<? foreach($semList as $user => $sem):?>
    <?= \Studip\Button::create(_($user), '', array('onclick' => "importPortfolio('$sem')", 'id' => $sem)) ?><br/>


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
      var data = exportData; //export data
      console.log("###exportPath:");
      console.log(data);
      path = data.substring(data.lastIndexOf("<path>")+6,data.lastIndexOf("</path>"));
      $('#path').val(path);
      $('#userportfolios').css("display", "block"); 
      $('#export').css("display", "none"); 
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