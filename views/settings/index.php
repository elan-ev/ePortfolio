<!-- <?php if(!$supervisorId == NULL):?>

  <?php $supervisor = UserModel::getUser($supervisorId);
      echo $supervisor[Vorname].' '.$supervisor[Nachname].'<br/>';
   ?>

   <div class="avatar-container"><?= Avatar::getAvatar($supervisorId)->getImageTag(Avatar::NORMAL) ?></div>

<?php else: ?>
  <button data-toggle="modal" data-target="#addSupervisorModal" type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Supervisor hinzufuegen</button>
<?php endif;?> -->

<?php if (empty($viewerList))
  echo MessageBox::info('Es sind derzeit keine Zugriffsrechte in Ihrem Portfolio vergeben.');
?>

<table class="default">

  <caption>Zugriffsrechte</caption>
  <tr class="sortable">
    <th>Name</th>
    <?php foreach ($chapterList as $chapter):?>
      <th>
        <?php echo $chapter[title]; ?>
      </th>
    <?php endforeach; ?>
  </tr>

<tbody>

<?php
  
  //Freigaben fï¿½r Portfolio
  $SupervisorFreigaben = SettingsController::getPortfolioFreigaben($cid);

  # Prüfen ob eigenes Portfolio
  $eigenesPortfolio = SettingsController::eigenesPortfolio($cid);
 ?>

<?php if ($eigenesPortfolio == false): ?>
  <tr style="background-color: lightblue;">
    <td>
    <?= Avatar::getNobody()->getImageTag(Avatar::SMALL,
                                array('style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;', 'title' => 'Gruppen-Supervisoren')); ?>
                        Gruppen-Supervisoren         
                    </a>
    </td>

    <?php foreach ($chapterList as $chapter):?>
      <?php $hasAccess = EportfolioFreigabe::hasAccess($supervisorId, $cid, $chapter[id]); ?>
      <td onClick="setAccess('<?= $chapter[id]?>', '<?= $supervisorId ?>', this, '<?= $cid ?>');" class="righttable-inner">

        <?php if($hasAccess):?>
          <span id="icon-<?php echo $supervisorId.'-'.$chapter[id]; ?>" class="glyphicon glyphicon-ok" title='Zugriff sperren'><?= Icon::create('accept', 'clickable'); ?></span>
        <?php else :?>
          <span id="icon-<?php echo $supervisorId.'-'.$chapter[id]; ?>" class="glyphicon glyphicon-remove" title='Zugriff erlauben'><?= Icon::create('decline', 'clickable'); ?></span>
        <?php endif;?>

      </td>

      <?php endforeach; ?>
  </tr>
<?php endif; ?>

<?php $i = 1; ?>
 <?php foreach ($viewerList as $viewer):?>
   <tr>
     <td>
       
       <?php $userInfo = UserModel::getUser($viewer[user_id]);?>
         <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $userInfo['username']) ?>" >
                        <?= Avatar::getAvatar($viewer[user_id])->getImageTag(Avatar::SMALL,
                                array('style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;', 'title' => htmlReady($viewer['Vorname']." ".$viewer['Nachname']))); ?>
                        <?= htmlReady($viewer['Vorname']." ".$viewer['Nachname']) ?>         
                    </a>
       
       
       
       
       <a title='Nutzer Zugriff vollständig entziehen (Nutzer wird komplett aus Zugriffs-Liste entfernt)' onclick="deleteUserAccess('<?php echo $viewer[viewer_id] ?>', '<?php echo $cid ?>', this);">
          <?php echo Icon::create('trash', 'clickable') ?>
       </a>
     </td>
     <?php foreach ($chapterList as $chapter):?>

      <?php $viewer_id = $viewer[user_id]; ?>
      <?php $hasAccess = EportfolioFreigabe::hasAccess($viewer_id, $cid, $chapter[id]); ?>
      <td onClick="setAccess('<?= $chapter[id]?>', '<?= $viewer_id ?>', this, '<?= $cid ?>');" class="righttable-inner">

        <?php if($hasAccess):?>
          <span id="icon-<?php echo $viewer[viewer_id].'-'.$chapter[id]; ?>" class="glyphicon glyphicon-ok" title='Zugriff sperren'><?= Icon::create('accept', 'clickable'); ?></span>
        <?php else :?>
          <span id="icon-<?php echo $viewer[viewer_id].'-'.$chapter[id]; ?>" class="glyphicon glyphicon-remove" title='Zugriff erlauben'><?= Icon::create('decline', 'clickable'); ?></span>
        <?php endif;?>

      </td>

      <?php endforeach; ?>

    <?php $i = 1; ?>
   </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php


$tempURL = URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id');
 ?>

 <a href="<?php echo $tempURL ?>" class="multi_person_search_link" data-dialog="width=720;height=460;id=mp-search" data-dialogname="eindeutige_id" title="Personen zur Gruppe hinzufügen" data-js-form="<?php echo $tempURL ?>">
   <?= \Studip\Button::create('Zugriffsrechte vergeben', 'klickMichButton', array('data-dialogname' => 'eindeutige_id', 'data-js-form' => $tempURL)); ?>
 </a>

<!-- <hr>

<div class="personal-colors">
  <h5>Courseware Hintergrundfarbe</h5>
  <?php $color = SettingsController::getsettingsColor();?>
  <div style="">
    <div class="row" style="margin-left: 3px;">
      <div onclick="settingsColor(this)" data-color="#1abc9c" style="background-color:#1abc9c" class="pers-color-block"><i class="fa fa-check" aria-hidden="true"></i></div>
      <div onclick="settingsColor(this)" data-color="#e67e22" style="background-color:#e67e22" class="pers-color-block"><i class="fa fa-check" aria-hidden="true"></i></div>
      <div onclick="settingsColor(this)" data-color="#9b59b6" style="background-color:#9b59b6" class="pers-color-block"><i class="fa fa-check" aria-hidden="true"></i></div>
      <div onclick="settingsColor(this)" data-color="#f39c12" style="background-color:#f39c12" class="pers-color-block"><i class="fa fa-check" aria-hidden="true"></i></div>
      <div onclick="settingsColor(this)" data-color="#27ae60" style="background-color:#27ae60" class="pers-color-block"><i class="fa fa-check" aria-hidden="true"></i></div>
      <div onclick="settingsColor(this)" data-color="#c0392b" style="background-color:#c0392b" class="pers-color-block"><i class="fa fa-check" aria-hidden="true"></i></div>
    </div>
  </div>
</div> -->

<script type="text/javascript">
function rgb2hex(rgb) {
  rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
  function hex(x) {
      return ("0" + parseInt(x).toString(16)).slice(-2);
  }
  return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

var color = rgb2hex('<?php echo $color; ?>');
console.log(color);
$('div[data-color="'+color+'"] i').css('opacity', '1').attr('data-status', 'active');
</script>



<script type="text/javascript" src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js'; ?>"></script>
<script type="text/javascript">

  var cid = '<?php echo $cid; ?>';

  $( document ).ready(function() {


    $('#deleteModal').on('shown.bs.modal', function () {
      $('#deleteModal').focus()
    })

    // Portfolio Informationen ï¿½ndern
    $('#portfolio-info-trigger').click( function() {
      $(this).toggleClass('show-info-not');
      $('#portfolio-info-saver').toggleClass('show-info');
      $('.portfolio-info-wrapper').toggleClass('show-info');
      $('.portfolio-info-wrapper-current').toggleClass('show-info-not');
    })

    $('#portfolio-info-saver').click( function() {
      $(this).toggleClass('show-info');
      $('#portfolio-info-trigger').toggleClass('show-info-not');
      $('.portfolio-info-wrapper').toggleClass('show-info');
      $('.portfolio-info-wrapper-current').toggleClass('show-info-not');

      var valName = $("#name-input").val();
      var valBeschreibung = $("#beschreibung-input").val();

      $.ajax({
        type: "POST",
        url: "/studip/plugins.php/eportfolioplugin/settings?cid="+cid,
        data: {'saveChanges': 1, 'Name': valName, 'Beschreibung': valBeschreibung},
        success: function(data) {
          $('.wrapper-name').empty().append('<span>'+valName+'</span>');
          $('.wrapper-beschreibung').empty().append('<span>'+valBeschreibung+'</span>');
        }
      });

    })

    //Search Supervisor
    $('#inputSearchSupervisor').keyup(function() {
      var val = $("#inputSearchSupervisor").val();
      var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/livesearch');

      $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        data: {
          'val': val,
          'status': 'dozent',
          'searchSupervisor': 1,
        },
        success: function(json) {
          $('#searchResult').empty();
          _.map(json, output);
          console.log(json);

          function output(n) {
            $('#searchResult').append('<div onClick="setSupervisor(&apos;'+n.userid+'&apos;)" class="searchResultItem">'+n.Vorname+' '+n.Nachname+'<span class="pull-right glyphicon glyphicon-plus" aria-hidden="true"></span></div>');
          }
        }
      });
    });

    //Search Viewer
    $('#inputSearchViewer').keyup(function() {
      var val = $("#inputSearchViewer").val();
      var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/livesearch');

      var values = _.words(val);

      $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        data: {
          'val': values,
          'searchViewer': 1,
          'cid': cid,
        },
        success: function(json) {
          $('#searchResultViewer').empty();
            _.map(json, output);
            console.log(json);
            function output(n) {
              console.log(n.userid);
              $('#searchResultViewer').append('<div onClick="setViewer(&apos;'+n.userid+'&apos;)" class="searchResultItem">'+n.Vorname+' '+n.Nachname+'<span class="pull-right glyphicon glyphicon-plus" aria-hidden="true"></span></div>');
            }
        },
        error: function(json){
          console.log(json.responsetext);
          $('#searchResultViewer').empty();
          _.map(json, output);
          function output(n) {
            $('#searchResultViewer').append('<div onClick="setViewer(&apos;'+n.userid+'&apos;)" class="searchResultItem">'+n.Vorname+' '+n.Nachname+'<span class="pull-right glyphicon glyphicon-plus" aria-hidden="true"></span></div>');
          }
        }
      });
    });

  });

  function deleteUserAccess(userId, seminar_id, obj){
    $(obj).empty().append('<i style="color: #24437c;" class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
    var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings');
    console.log(userId);
    $.ajax({
      type: "POST",
      url: url,
      data: {
        'action': 'deleteUserAccess',
        'userId': userId,
        'seminar_id': seminar_id,
      },
      success: function(data) {
        console.log(data);
        $(obj).parents('tr').fadeOut();
      }
    });
  }

  function settingsColor(obj){
    var color = $(obj).css('background-color');
    var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings');
    console.log(color);
    $.ajax({
      type: "POST",
      url: url,
      data: {
        'action': 'setsettingsColor',
        'color': color,
        'cid': '<?php echo $cid; ?>',
      },
      success: function(data) {
        //console.log(data);
        $('i[data-status="active"]').css('opacity', '0').removeAttr('data-status');
        var activate = $(obj).find('i');
        $(activate).attr('data-status', 'active').css('opacity', '1');
      }
    });
  }

  
  function setAccess(id, viewerId, obj, cid){
  var status = $(obj).children('span').hasClass('glyphicon-ok');
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings/setAccess/'+viewerId+ '/' +cid+ '/' +id +'/' +!status);
  $.ajax({
    type: "POST",
    url: url,
    success: function(data) {
     if (status === false) {
        $(obj).empty().append('<span class="glyphicon glyphicon-ok"><?php echo  Icon::create('accept', 'clickable'); ?></span>');
      } else {
        $(obj).empty().append('<span class="glyphicon glyphicon-remove"><?php echo  Icon::create('decline', 'clickable'); ?></span>');
      }

    }
  });
}

</script>
