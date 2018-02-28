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
  //supervisor Zeile
  //Supervisor Informationen
  $supervisorId = SettingsController::getSupervisorGroupOfPortfolio($cid);
  //$supervisor = UserModel::getUser($supervisorId);
  //$supervisorName = $supervisor[Vorname].' '.$supervisor[Nachname];

  //Freigaben für Portfolio
  $SupervisorFreigaben = SettingsController::getPortfolioFreigaben($cid);

  # Prüfen ob eigenes Portfolio
  $eigenesPortfolio = SettingsController::eigenesPortfolio($cid);
 ?>

<?php if ($eigenesPortfolio == false): ?>
  <tr style="background-color: lightblue;">
    <td>
      <img style="border-radius: 30px; width: 15px;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/<?php echo $supervisorId; ?>_small.png" onError="defaultImg(this);">
      Supervisoren
    </td>

    <?php foreach ($chapterList as $chapter):?>
      <?php if($SupervisorFreigaben[$chapter[id]] == 1): ?>
        <td id="chapter<?php echo $chapter[id]?>" onclick="freigebenSupervisorGroup('<?php echo $chapter[id]; ?>', '<?php echo $cid; ?>');"><?php echo  Icon::create('accept', 'clickable'); ?></td>
      <?php else: ?>
        <td id="chapter<?php echo $chapter[id]?>" onclick="freigebenSupervisorGroup('<?php echo $chapter[id]; ?>', '<?php echo $cid; ?>');"><?php echo  Icon::create('decline', 'clickable'); ?></td>
      <?php endif; ?>
    <?php endforeach; ?>
  </tr>
<?php endif; ?>

<?php $i = 1; ?>
 <?php foreach ($viewerList as $viewer):?>
   <tr>
     <td>
       
       <?php $userInfo = UserModel::getUser($viewer[viewer_id]);?>
         <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $userInfo['username']) ?>" >
                        <?= Avatar::getAvatar($viewer[viewer_id], $userInfo['username'])->getImageTag(Avatar::SMALL,
                                array('style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>
                        <?= htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']) ?>         
                    </a>
       
       
       
       
       <a onclick="deleteUserAccess('<?php echo $viewer[viewer_id] ?>', '<?php echo $cid ?>', this);">
          <?php echo Icon::create('trash', 'clickable') ?>
       </a>
     </td>
     <?php $access = settingsController::getEportfolioAccess($viewer[viewer_id], $cid);?>
     <?php foreach ($chapterList as $chapter):?>

      <?php $viewer_id = $viewer[viewer_id]; ?>
      <td onClick="setAccess(<?php echo $chapter[id]?>, '<?php echo $viewer_id ?>', this); checkIcon('<?php echo $viewer[viewer_id]?>', <?php echo $chapter[id]; ?>);" class="righttable-inner">

        <?php if($access[$chapter[id]] == 1):?>
          <span id="icon-<?php echo $viewer[viewer_id].'-'.$chapter[id]; ?>" class="glyphicon glyphicon-ok" aria-hidden="true"></span>
        <?php elseif($access[$chapter[id]] == 0):?>
          <span id="icon-<?php echo $viewer[viewer_id].'-'.$chapter[id]; ?>" class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <?php endif;?>

      </td>

      <?php endforeach; ?>

    <?php $i = 1; ?>
   </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php
$mp = MultiPersonSearch::get('eindeutige_id')
  ->setLinkText(_('Personen hinzufügen'))
  ->setTitle(_('Personen zur Gruppe hinzuf&uuml;gen'))
  ->setSearchObject(new StandardSearch('user_id'))
  ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/settings/addZugriff/'.$cid))
  ->render();

$tempURL = URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id');
 ?>

 <a href="<?php echo $tempURL ?>" class="multi_person_search_link" data-dialog="width=720;height=460;id=mp-search" data-dialogname="eindeutige_id" title="Personen zur Gruppe hinzuf&amp;uuml;gen" data-js-form="<?php echo $tempURL ?>">
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

<!-- Modal Suche Supervisor -->
<div class="modal fade" id="addSupervisorModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Supervisor hinzufügen</h4>
      </div>
      <div class="modal-body" id="modalDeleteBody">

          <p>
            <div class="input-group" style="margin-bottom:20px;">
              <div class="input-group-addon"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></div>
              <input type="text" class="form-control" id="inputSearchSupervisor" placeholder="Name des Supervisors">
            </div>

            <div id="searchResult"></div>

          </p>


      </div>
    </div>
  </div>
</div>


<script type="text/javascript" src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js'; ?>"></script>
<script type="text/javascript">

  var cid = '<?php echo $cid; ?>';

  $( document ).ready(function() {


    $('#deleteModal').on('shown.bs.modal', function () {
      $('#deleteModal').focus()
    })

    // Portfolio Informationen ändern
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

  function freigebenSupervisorGroup(selected, cid){
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

</script>
