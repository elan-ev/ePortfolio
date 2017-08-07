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

  .courseware_infobox_owner {
    border: 2px solid black;
    padding: 5px;
  }


  #nav_course_files, #nav_course_forum2, #nav_course_mooc_progress {
    display: none;
  }

  .cktoolbar {
    max-width: 100% !important;
    width: 100%;
  }

  .cke_reset {
    width: 100% !important;
    height: 150px!important;
  }

  .fakeButton {
    background: white;
    border: 1px solid #28497c;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    color: #28497c;
    cursor: pointer;
    display: inline-block;
    font-family: "Lato",sans-serif;
    font-size: 14px;
    line-height: 130%;
    margin: .8em .6em .8em 0;
    min-width: 100px;
    overflow: visible;
    padding: 5px 15px;
    position: relative;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    white-space: nowrap;
    width: auto;
    -webkit-transition: none;
    -moz-transition: none;
    -o-transition: none;
    transition: none;
  }

  .fakeButton:hover{
    background: #28497c;
    color: white;
    outline: 0;
  }
</style>


<script type="text/javascript" src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/jquery.js'; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/mustache.min.js'; ?>"></script>
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

<script id="templateOwnerTrue" type="x-tmpl-mustache">
  <div class="courseware_infobox_owner">

    <p>
     </p>

    <p>Liste der berechtigten Personen:
        {{#users}}

          <div>{{firstname}} {{lastname}}</div>

        {{/users}}

    </p>
    <button class="fakeButton" onclick='freigeben(<?php echo $selected; ?>, `<?php echo $cid ?>`);'>Freigeben</button>

  </div>
</script>

<script id="templateOwnerFalse" type="x-tmpl-mustache">
  <div class="courseware_infobox_owner">
    <p>Besitzer: {{firstname}} {{lastname}}</p>
    <?php $link = URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('cid' => $cid)); ?>
    <a href="<?php echo $link; ?>">
      <button class="fakeButton">Zurück</button>
    </a>
  </div>
</script>

<script type="text/javascript">

  $(document).ready(function(){
    $('#courseware').append("<button onclick='freigeben(<?php echo $selected; ?>, `<?php echo $cid ?>`);'>Freigeben</button>");

    infobox();
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

  function infobox(){

    var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/coursewareinfoblock');
    var selected = <?php if(!$_GET["selected"]){echo 0;} else{echo $selected;} ?>;

    console.log(selected);

    $.ajax({
      url: url,
      type: "POST",
      data: {
        infobox: "infobox",
        cid: "<?php echo $cid; ?>",
        userid: "<?php echo $userId; ?>",
        selected: selected,
      },
      success: function(data){

        data = JSON.parse(data);
        console.log(data);

        if (data.owner == true) {
          var template = $('#templateOwnerTrue').html();
          $('#courseware').prepend(Mustache.render(template, data));
        } else {
          var template = $('#templateOwnerFalse').html();
          $('#courseware').prepend(Mustache.render(template, data));
        }

      }
    });

  }

</script>
