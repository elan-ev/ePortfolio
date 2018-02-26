<?php

//print_r($GLOBALS);

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

  .courseware_infobox_owner {
    background-color: #e7ebf1;
    padding: 6px 10px 0;
    margin-bottom: 20px;
    margin-right: 100px;
  }

  .viewer_icon {
    position: relative;
    display: inline-block;
    margin-right: 1px;
  }

  .viewer_icon .tooltiptext {
    visibility: hidden;
    width: 120px;
    background-color: #28497c;
    color: #fff;
    text-align: center;
    border-radius: 3px;
    padding: 1px 0;

    /* Position the tooltip */
    position: absolute;
    z-index: 1;
}

.viewer_icon:hover .tooltiptext {
    visibility: visible;
}

.viewer_icon .tooltiptext {
    width: 120px;
    bottom: 34px;
    left: 50%;
    margin-left: -60px; /* Use half of the width (120/2 = 60), to center the tooltip */
}

.viewer_icon .tooltiptext::after {
    content: " ";
    position: absolute;
    top: 100%; /* At the bottom of the tooltip */
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #28497c transparent transparent transparent;
}


/*Overflow Visible*/
#layout_content {
  overflow: visible!important;
}
</style>


<script type="text/javascript" src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/jquery.js'; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/mustache.min.js'; ?>"></script>
<script type="text/javascript">
  $(document).ready(function(){
    console.log("modifier.php loaded");
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

    <div style="display: inline; position: relative; top: -6px; font-size: 14px; font-weight: bold; margin-right: 10px;">Lesen dürfen:</div>

      {{#supervisorId}}
        <div class="viewer_icon">
          <img style="border-radius: 30px; width: 21px; border: 1px solid #28497c;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/{{supervisorId}}_small.png" onError="defaultImg(this);">
          <span class="tooltiptext">{{supervisorFistname}} {{supervisorLastname}}</span>
        </div>
      {{/supervisorId}}

        {{#users}}

          <div class="viewer_icon">
            <img style="border-radius: 30px; width: 20px; border: 1px solid grey;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/{{userid}}_small.png" onError="defaultImg(this);">
            <span class="tooltiptext">{{firstname}} {{lastname}}</span>
          </div>

        {{/users}}

        <div style="display: inline; font-size: 24px; position: relative; top: -3px;margin-left:2px;">
          <a href="<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/settings?cid='.$cid); ?>">
            <?=Icon::create('admin', 'clickable', ['title' => sprintf(_('Zugriffsrechte bearbeiten'))])?>
          </a>
        </div>

  </div>
</script>

<script id="templateOwnerFalse" type="x-tmpl-mustache">
  <div class="courseware_infobox_owner">
  <div style="margin-top: 15px;margin-left:10px;float: left;">
    <div style="display: inline; position: relative; top: -5px;font-size: 14px; font-weight: bold;margin-right: 5px;">Besitzer:</div>
    <img style="border-radius: 30px; width: 18px; border: 1px solid #28497c;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/{{userId}}_small.png" onError="defaultImg(this);">
    <div style="display: inline;position: relative; top: -5px;">{{firstname}} {{lastname}}</div>
    <?php $link = URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('cid' => $cid)); ?>
  </div>
    <a style="float: right;" href="<?php echo $link; ?>">
      <button class="fakeButton">Zur&uuml;ck</button>
    </a>
    <div style="clear: both;"></div>
  </div>
</script>

<script type="text/javascript">

  $(document).ready(function(){
    var color;

    getsettingsColor();
    infobox();
    authorModeColor();
    changeTitle();
  });

  function changeTitle(){
    $('span[title="Courseware"]').html('ePortfolio');
  }

  function authorModeColor() {
    $('.author').click(function (){
      $('.active-section').css('background', '#fff');
    });

    $('.student').click(function (){
      $('.active-section').css('background', color);
    });
  }

  function freigeben(selected, cid){
    console.log(selected + " " + cid);
    var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin');
    $('td[id="chapter'+selected+'"]').empty().prepend('<i style="color: #24437c;" class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
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
        if (data == true) {
          $('td[id="chapter'+selected+'"]').empty().prepend('<?php echo  Icon::create('accept', 'clickable'); ?>');
        } else {
          $('td[id="chapter'+selected+'"]').empty().prepend('<?php echo  Icon::create('decline', 'clickable'); ?>');
        }

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

        console.log(data);
        data = JSON.parse(data);
        console.log(data);

        if (data.owner == true) {
          var template = $('#templateOwnerTrue').html();
          $('#courseware').prepend(Mustache.render(template, data));
        } else {
          var template = $('#templateOwnerFalse').html();
          $('#courseware').prepend(Mustache.render(template, data));
        }

      },
      error: function(data){
          console.log(data);
      }
    });

  }

  function defaultImg(img) { //setzt default Profilbild falls keins vorhanden
    img.src = "<?php echo $GLOBALS[DYNAMIC_CONTENT_URL]; ?>/user/nobody_small.png";
  }

  function getsettingsColor(){
    var cid = '<?php echo $_GET["cid"] ?>';
    var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin', {cid, cid});

    $.ajax({
      url: url,
      type: "POST",
      data: {
        action: "getsettingsColor",
        cid: "<?php echo $cid; ?>",
      },
      success: function(data){
        // data = rgb2hex(data);
        color = data;
        $('.active-section').css('background', data);
        $('.active-subchapter').css('background-color', 'rgba(0,0,0,0)');
      }
    });
  }

</script>
