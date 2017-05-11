<?php

//print('modifier.php active');

//set variables
$cid = $_GET["cid"];
$userId = $GLOBALS["user"]->id;
$selected = $_GET["selected"];

$workingArray = $this->getAccess($cid, $userId);
$workingArray = unserialize($workingArray);
$workingArray = json_encode($workingArray);

?>
<style media="screen">
  .cke_chrome {
    min-height: 0px!important;
  }

  #nav_course_files, #nav_course_forum2, #nav_course_mooc_progress {
    display: none;
  }
</style>
<script type="text/javascript" src="/studip/plugins_packages/uos/EportfolioPlugin/assets/js/jquery.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    console.log("ready");
    $('li[id="nav_eportfolioplugin"] img').attr('src', 'http://studip3g-test.rz.uni-osnabrueck.de/studip/assets/images/icons/white/admin.svg');
    var workingArray = <?php echo $workingArray ?>;
    console.log(workingArray);
    $.each(workingArray, function(key, value){
      console.log(key +": "+value);
      if(value == 0){
        $('*[data-blockid='+ key +']').remove();
      }
    });
  });

  $('#nav_course_files, #nav_course_mooc_progress').css('display', 'none');
</script>

<script type="text/javascript">

  $(document).ready(function(){
    $('#courseware').append("<button onclick='freigeben(<?php echo $selected; ?>, `<?php echo $cid ?>`);'>Freigeben</button>");
  });

  function freigeben(selected, cid){
    console.log(selected + " " + cid);
    var url = "/studip/plugins.php/eportfolioplugin";
    $.ajax({
      url: url,
      type: 'POST',
      data: {
        type: "freigeben",
        selected: selected,
        cid: cid
      },
      success: function(data){
        console.log(data);
      }

    });
  }

</script>
