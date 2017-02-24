<?php

print('modifier.php active');

//set variables
$cid = $_GET["cid"];
$userId = $GLOBALS["user"]->id;

$workingArray = $this->getAccess($cid, $userId);
$workingArray = unserialize($workingArray);
$workingArray = json_encode($workingArray);

?>
<script type="text/javascript" src="/studip/plugins_packages/Universitaet Osnabrueck/EportfolioPlugin/assets/js/jquery.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    var workingArray = <?php echo $workingArray ?>;
    $.each(workingArray, function(key, value){
      console.log(key +": "+value);
      if(value == 0){
        $('*[data-blockid='+ key +']').remove();
      }

    });
  });


</script>
