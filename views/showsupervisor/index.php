<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

  <style media="screen">

    .widget-list, .widget-links li {
      position: relative;
    }

    .active-link {
      background-color: #a9b6cb;
      box-shadow: inset 0 0 0 1px #7e92b0;
      color: #fff!important;
    }

    .active-link::before {
      border: 10px solid rgba(126,146,176,0);
      content: "";
      height: 0;
      width: 0;
      position: absolute;
      border-left-color: #7e92b0;
      left: 100%;
      top:50%;
      margin-top: -10px;
    }

    .legend {
      background: rgba(0, 0, 0, 0.01);
      border: 1px solid rgba(0, 0, 0, 0.2);
      max-width: 800px;
      padding: 5px 10px;
      margin-top: 30px;
    }

    .legend ul li {
      margin: 0px;
      list-style: none;
    }

    .legend ul {
      padding:0px;
    }

    tr {
      border-bottom: 1px solid #e2e3e5!important;
    }

    td, th {
      text-align: center;
    }

    .widget-custom{
      border: 1px solid #d0d7e3;
            margin-bottom: 20px;
    }

    .widget-custom-head{
      -webkit-box-sizing: border-box;
      -moz-box-sizing: border-box;
      box-sizing: border-box;
      background-color: #e7ebf1;
      color: #28497c;
      font-size: 1.1em;
      font-weight: bold;
      line-height: 2em;
      padding: 0 1ex;
      text-align: left;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .widget-custom-content {
      padding: 1ex;
    }

  </style>

</head>

<!-- <button type="button" class="btn btn-default" data-toggle="modal" data-target="#myModal">
 Neue Supervisionsgruppe erstellen
</button> -->

<h1><?php echo showsupervisorcontroller::getCourseName($id); ?></h1>

<?php showsupervisorcontroller::getTemplates($id); ?>

<div>

      <!-- <select class="" id="tempselector" name="template">
        <?php  $templates = showsupervisorcontroller::getTemplates($id); ?>
        <?php foreach ($templates as $key => $value):?>
          <option value="<?php echo $value[id] ?>"><?php echo $value[temp_name] ?></option>
        <?php endforeach; ?>
      </select>
      <?= \Studip\Button::create('Hinzuf�gen', 'button', array('type' => 'button', 'onclick' => 'addTemp()')); ?> -->

      <div id="wrapper_table_tamplates">
        <h4>Portfoliotemplate hinzuf&uuml;gen</h4>

        <table id="table_templates" class="default">
          <colgroup>
            <col width="30%">
            <col width="60%">

          </colgroup>
          <thead>
            <tr class="sortable">
              <th>Portfolio-Name</th>
              <th>Beschreibung</th>
              <th>Aktionen</th>

            </tr>
          </thead>

          <tbody>
            <?php $temps = ShowsupervisorController::getTemplates();
              foreach ($temps as $key):?>
              <?php $thisPortfolio = new Seminar($key); ?>
              <?php if (ShowsupervisorController::checkTemplate($id, $key) == false): ?>
                <tr>
                  <td><?php echo $thisPortfolio->getName(); ?></td>
                  <td><?php echo ShowsupervisorController::getCourseBeschreibung($key); ?></td>
                  <td style="text-align: center;">
                      <a href="<?php echo URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $key)); ?>"><?php echo Icon::create('edit', 'clickable') ?></a>
                      <a onclick="triggerModalCreate('<?php echo $key; ?>')" href="#"><?php echo Icon::create('add', 'clickable') ?></a>
                  </td>
                </tr>
              <?php endif; ?>

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

  <?php
    $groupTemplates = ShowsupervisorController::getGroupTemplates($id);
    if (empty($groupTemplates[0])):
  ?>

    <h4>Gruppenmitglieder</h4>

    <?php if (ShowsupervisorController::isThereAnyUser() == false):?>
        <?php echo MessageBox::info('Es sind noch keine Nutzer in der der Gruppe eingetragen'); ?>
    <?php else: ?>

      <table class="default">
        <colgroup>
          <col width="30%">
          <col width="60%">
        </colgroup>
        <tr>
          <th>Name</th>
          <th></th>
          <th>Aktionen</th>
        </tr>
        <?php foreach ($groupList as $user):?>
          <tr>
            <td>
              <img style="border-radius: 30px; width: 21px; border: 1px solid #28497c;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/<?php echo $user; ?>_small.png" onError="defaultImg(this);">
              <?php $userInfo = UserModel::getUser($user);?><?php echo $userInfo['Vorname']." ".$userInfo['Nachname']; ?>
            </td>
            <td></td>
            <td style="text-align:center;">
              <?php echo  Icon::create('person', 'clickable'); ?>
              <a onclick="deleteUserFromGroup('<?php echo $user; ?>', this);"><?php echo  Icon::create('trash', 'clickable'); ?></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

    <?php endif; ?>

    <?php else: ?>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
      <?php $templistid = showsupervisorcontroller::getGroupTemplates($id);?>
      <?php foreach ($templistid as $key => $value): ?>
        <?php $template = new Seminar($value);?>
        <li role="presentation"><a href="#<?php echo $value; ?>" aria-controls="<?php echo $value; ?>" role="tab" data-toggle="tab"><?php echo $template->getName(); ?></a></li>
      <?php endforeach; ?>
    </ul>
    <!-- Tab panes -->

    <div class="tab-content">
      <?php $templistid = showsupervisorcontroller::getGroupTemplates($id); ?>
      <?php foreach ($templistid as $key => $value): ?>
        <?php $tempid = $value ?>
        <div role="tabpanel" class="tab-pane" id="<?php echo $value; ?>">
          <table class="default">
            <tr>
              <th style="width: 200px;border-bottom: 1px solid;">Name</th>
              <?php
                $q = ShowsupervisorController::getChapters($value);
                foreach ($q as $key): ?>
                  <th style="width: 100px; border-bottom: 1px solid;"><?php print_r($key[0]); ?></th>
              <?php endforeach; ?>
            </tr>
            <?php foreach ($groupList as $key):?>
              <tr>
                <td style="text-align: left;">
                  <img style="border-radius: 30px; width: 21px; border: 1px solid #28497c;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/<?php echo $key; ?>_small.png" onError="defaultImg(this);">
                  <?php $supervisor = UserModel::getUser($key);
                  $userid = $key;
                      echo $supervisor[Vorname].' '.$supervisor[Nachname];
                   ?>
                </td>
                <?php $getsemid = DBManager::get()->query("SELECT Seminar_id FROM eportfolio WHERE owner_id = '$key' AND template_id = '$tempid'")->fetchAll();
                $getsemid = $getsemid[0][0];
                ?>

                <?php
                //$q = DBManager::get()->query("SELECT title, id FROM mooc_blocks WHERE type = 'Chapter' AND seminar_id = 'is22plkvtlt3ms6vvuwjsrwfuwohruq9'")->fetchAll();
                $status = DBManager::get()->query("SELECT templateStatus FROM eportfolio WHERE Seminar_id = '$getsemid'")->fetchAll();
                $status = $status[0][0];

              //  $q = DBManager::get()->query("SELECT title, id FROM mooc_blocks WHERE type = 'Chapter' AND seminar_id = '$getsemid'")->fetchAll();
              //  $q = ShowsupervisorController::getChapters($tempid);
              $q = DBManager::get()->query("SELECT title, id FROM mooc_blocks WHERE seminar_id = '$getsemid' AND type = 'Chapter'")->fetchAll();

              //�bergangsl�sung Kapitel 1 & Kapitel 2 m�ssen noch entfernt werden
              unset($q[0]);
              unset($q[1]);

                foreach ($q as $key => $value): ?>

                    <?php $t = DBManager::get()->query("SELECT freigaben_kapitel FROM eportfolio WHERE Seminar_id = '$getsemid'")->fetchAll();

                    $freigaben_kapitel = json_decode($t[0][0], true);
                    ?>

                    <td><?php $idNew = $value[id];
                      if($freigaben_kapitel[$idNew]):?>
                        <?php $link = URLHelper::getLink("plugins.php/courseware/courseware", array('cid' => $getsemid , 'selected' => $idNew));?>
                        <a href="<?php echo $link; ?>">
                          <?php echo  Icon::create('accept', 'clickable'); ?>
                        </a>
                      <?php else: ?>
                        &nbsp;
                      <?php endif; ?>
                    </td>


                <?php endforeach; ?>

              </tr>
            <?php endforeach; ?>
          </table>

          <!-- <button type="button" name="button" onclick="deletetemplate(<?php echo $tempid; ?>)">Vorlage f�r diese Gruppe l�schen</button> -->
          <!--<?= \Studip\Button::create('Vorlage f�r diese Gruppe l�schen', 'button', array('type' => 'button', 'onclick' => 'deletetemplate('.$tempid.')')); ?>-->

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php if(!$id): ?>

  <div class="panel panel-primary">
  <div class="panel-heading">
    Gruppen erstellen
  </div>

  <?php echo MessageBox::info('Aktuell haben Sie noch keine Gruppen erstellt. Bitte erstellen Sie zun�chst ein Gruppe um mit der Verwaltung fortzufahren.'); ?>

</div>

<?php endif; ?>

<?php
  $mp = MultiPersonSearch::get('eindeutige_id')
    ->setLinkText(_('Personen hinzufügen'))
    ->setTitle(_('Personen zur Gruppe hinzuf&uuml;gen'))
    ->setSearchObject(new StandardSearch('user_id'))
    ->setJSFunctionOnSubmit('addUserToGroup')
    ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor', array('id' => $groupid, 'action' => 'addUsersToGroup')))
    ->render();
 ?>

<?php if (empty(ShowsupervisorController::getGroupTemplates($id))):?>
   <a href="<?php echo URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id'); ?>" class="multi_person_search_link" data-dialog="width=720;height=460;id=mp-search" data-dialogname="eindeutige_id" title="Personen zur Gruppe hinzuf&amp;uuml;gen" data-js-form="<?php echo URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id'); ?>">
     <?= \Studip\Button::create('Personen hinzufügen', 'klickMichButton', array('data-dialogname' => 'eindeutige_id', 'data-js-form' => URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id'))); ?>
   </a>
<?php endif; ?>

<!-- Legende -->
<div class="legend">
  <ul>
    <li><?php echo  Icon::create('accept', 'clickable'); ?>  Kapitel/Implus freigeschaltet</li>
    <li><?php echo  Icon::create('accept+new', 'clickable'); ?></i>  Kapitel freigeschaltet und �nderungen seit ich das letzte mal reingeschaut habe</li>
    <li><?php echo  Icon::create('file', 'clickable'); ?>  Supervisionsanliegen freigeschaltet</li>
    <li><?php echo  Icon::create('forum', 'clickable'); ?>  Resonanz gegeben</li>
  </ul>
</div>

<!-- <div id="myModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Neue Supervisionsgruppe erstellen</h4>
      </div>
      <div class="modal-body">

        <form id="createGroupForm">

          <label>
            <span class="required">Name</span>
            <input type="text" name="name" id="wizard-name" size="75" maxlength="254" value="" required="" aria-required="true" aria-invalid="true">
          </label>

        <label>
          <span>Beschreibung</span>
          <textarea name="description" id="wizard-description" cols="75" rows="4"></textarea>
        </label>

      </form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onClick="createGroup();">Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="userInfoModel" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">

        <div id="dataOutputer">

        </div>


      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal-area"></div>

<script type="text/javascript">
$('#myModal').on('shown.bs.modal', function () {
$('#myInput').focus()
})

if ( $('#table_templates').find("td").length === 0 ) {
    $('#wrapper_table_tamplates').css('display', 'none');
}

function createGroup(){
  var name        = $('#wizard-name').val();
  var description = $('#wizard-description').val();
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor', {create:1});
  console.log('<?php echo $ownerid; ?>');
  $.ajax({
    type: "POST",
    url: url,
    data: {
      name: name,
      description: description,
      ownerid: "<?php echo $ownerid; ?>"
    },
    success: function(data) {
      console.log(data);
      // alert(data);
      // var neu = $("#createGroupForm").serialize();
      var id = data;
      // console.log(id);
      window.document.location.href = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor', {id: id});
      //window.document.location.href = "/studip/portfolio/plugins.php/eportfolioplugin/showsupervisor?id="+id;
    }
  });
}

function addUserToGroup(item_id, item_name, item_firstname, item_userid){
  console.log(item_id[0]);
}

function getUserData(id){
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/ajaxsupervisor', {userId: id});
  $.ajax({
    type: "POST",
    url: url,
    dataType: 'JSON',
    success: function(data) {
      $('#userInfoModel').mclassodal('toggle');
      console.log(data);
      _.forEach(data, function(value){
        console.log(value);
      });
    }
  });
}

$('#myTabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})

function addTemp(){
  const tempid = $('#tempselector').val();
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');

  $.ajax({
    type: "POST",
    url: url,
    data: {
      type: 'addTemp',
      groupid: '<?php echo $id ?>',
      tempid: tempid
    },
    success: function(data){
      if (data == "  created") {
        createPortfolio(tempid);
      }
    }
  });
}

function createPortfolio(master){
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');
  $('.content').empty().css({
    'background': 'none',
    'text-align': 'center',
    'padding': '20px 0',
  }).append('<i style="color: #24437c;" class="fa fa-circle-o-notch fa-3x fa-spin fa-fw"></i>');
  $('.ui-dialog-buttonpane').remove();
  $('.ui-dialog-titlebar-close').css('display', 'none');
  console.log(master);
  $.ajax({
    type: "POST",
    url: url,
    data: {
      type: 'createPortfolio',
      groupid: '<?php echo $id ?>',
      master: master
    },
    success: function(data){
      console.log(data);
      targets = JSON.parse(data);
      exportPortfolio(master, targets);
    }
  });
}

function exportPortfolio(master, targets){

  //debug
  console.log(master);
  console.log(targets);

  urlexport = STUDIP.URLHelper.getURL('plugins.php/courseware/exportportfolio', {cid: master}); //url export

  $.ajax({
    type: "GET",
    url: urlexport,
    success: function(exportData){
      var xml = exportData; //export data

      targets.forEach(function(target) {

        urlimport = STUDIP.URLHelper.getURL('plugins.php/courseware/importportfolio', {cid: target}); //url import

        $.ajax({
          type: "POST",
          url: urlimport,
          data: {xml: xml},
          success: function(importData){
            console.log(importData);
            closeModal();
            location.reload();
          }
        });
      });

    }
  });
}

function deleteUserFromGroup(userid, obj) {
  var deleteThis    = $(obj).parents('tr');
  var tdParent      = $(obj).parents('td');
  var urlDeleteUser = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');

  $(obj).parents('td').append('<i style="color: #24437c;" class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
  $(obj).remove();


  $.ajax({
    type: "POST",
    url: urlDeleteUser,
    data: {
      action: 'deleteUserFromGroup',
      userId: userid,
      seminar_id: '<?php echo $id ?>',
    },
    success: function(data){
      $(deleteThis).fadeOut();
    }
  });
}

function deletetemplate(tempid){
  var c = confirm("Es werden alle bestehenden ePortfolios dieses Templates gelöscht! Möchten Sie fortfahren?");
  if (c == true){

    var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');
    $.ajax({
      type: "POST",
      url: url,
      data: {
        type: 'delete',
        tempid: tempid,
        groupid: '<?php echo $id ?>'
      },
      success: function(data){
        console.log(data);
      }
    });

  } else {
    console.log("cancel");
  }
}


$(document).ready(
  function(){
    $('div[role="tabpanel"]:first').addClass('active');
  }
);

function testfunction(){
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');
  $.ajax({
    type: "POST",
    url: url,
    data: {
      type: 'addTemplateTest'
    },
    success: function(data){
      console.log(data);
    }
  });
}

// function testfunctionbeta(){
//
//
//   var url = STUDIP.URLHelper.getURL('plugins.php/courseware/export?cid=43ff6d96a50cf30836ef6b8d1ea60667');
//
//   $.ajax({
//     type: "GET",
//     url: '<?php echo URLHelper::getLink('plugins.php/courseware/exportportfolio', array('cid' => '43ff6d96a50cf30836ef6b8d1ea60667', 'uniqid' => uniqID())); ?>',
//     success: function(exportData){
//
//       var urlMembers = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');
//       $.ajax({
//         type: 'POST',
//         url: urlMembers,
//         data: {
//           type: 'getGroupMember',
//           id: '<?php echo $id; ?>'
//         },
//         success: function(members){
//           members = JSON.parse(members);
//           console.log(members);
//         }
//       });
//
//       // var xml = exportData;
//       //
//       // $.ajax({
//       //   type: "POST",
//       //   url: '<?php echo URLHelper::getLink('plugins.php/courseware/importportfolio', array('cid' => '4fa67ff89828d7c6926a0e23c04aa283', 'uniqid' => uniqID())); ?>',
//       //   data: {xml: xml},
//       //   success: function(importData){
//       //     console.log(importData);
//       //   }
//       // });
//
//
//     }
//   });
// }

function vechta(){
  var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');
  $.ajax({
    type: "POST",
    url: url,
    data: {
      type: 'addTemplateVechta'
    },
    success: function(data){
      console.log(data);
    }
  });
}

var unique = function(origArr) {
    var newArr = [],
        origLen = origArr.length,
        found, x, y;

    for (x = 0; x < origLen; x++) {
        found = undefined;
        for (y = 0; y < newArr.length; y++) {
            if (origArr[x] === newArr[y]) {
                found = true;
                break;
            }
        }
        if (!found) {
            newArr.push(origArr[x]);
        }
    }
    return newArr;
};

var uniqID = function() {
    var ts = +new Date;
    var tsStr = ts.toString();

    var arr = tsStr.split('');
    var rev = arr.reverse();


    var filtered = unique(rev);

    return filtered.join('');

}

function defaultImg(img) { //setzt default Profilbild falls keins vorhanden
  img.src = "<?php echo $GLOBALS[DYNAMIC_CONTENT_URL]; ?>/user/nobody_small.png";
}

function triggerModalCreate(id){
  var template = $('#modal-template').html();
  Mustache.parse(template);   // optional, speeds up future uses
  var rendered = Mustache.render(template, {id: id, titel: "Template verteilen", text: "Wollen Sie dieses Template wirklich an die aktuellen Gruppenmitglieder verteilen?"});
  $('.modal-area').html(rendered);
}

function closeModal(){
  $('.modal-area').empty();
}

function modalneueGruppe(){
  var template = $('#modal-template-neueGruppe').html();
  Mustache.parse(template);   // optional, speeds up future uses
  var rendered = Mustache.render(template, {titel: 'Neue Gruppe erstellen'});
  $('.modal-area').html(rendered);
}

</script>

<script id="modal-template" type="x-tmpl-mustache">
   <div class="modaloverlay">
      <div class="create-question-dialog ui-widget-content ui-dialog studip-confirmation">
          <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
              <span>{{titel}}</span>
              <a onclick="closeModal();" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close">
                  <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                  <span class="ui-button-text">Schliessen</span>
              </a>
          </div>
          <div class="content ui-widget-content ui-dialog-content studip-confirmation">
              <div class="formatted-content">{{text}}</div>
          </div>
          <div class="buttons ui-widget-content ui-dialog-buttonpane">
              <div class="ui-dialog-buttonset">
                <a class="accept button" onclick="createPortfolio('{{id}}')">Ja</a>
                <a class="cancel button" onclick="closeModal();">Nein</a>
              </div>
          </div>
      </div>
  </div>
</script>

<script id="modal-template-neueGruppe" type="x-tmpl-mustache">
   <div class="modaloverlay">
      <div class="create-question-dialog ui-widget-content ui-dialog studip-confirmation">
          <div style="background-color: #28497c;" class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
              <span style="color:#fff;">{{titel}}</span>
              <a style="color:#fff;" onclick="closeModal();" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close">
                  <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                  <span class="ui-button-text">Schliessen</span>
              </a>
          </div>
          <div style="background:none;padding: 10px;" class="content ui-widget-content ui-dialog-content studip-confirmation">
              <div class="formatted-content">{{text}}</div>
              <form id="createGroupForm">

                <label>
                  <span class="required">Name</span>
                  <input style="width: 100%;" type="text" name="name" id="wizard-name" maxlength="254" value="" required="" aria-required="true" aria-invalid="true">
                </label>

              <label>
                <span>Beschreibung</span>
                <textarea style="width: 100%;" name="description" id="wizard-description" cols="75" rows="4"></textarea>
              </label>

            </form>
          </div>
          <div class="buttons ui-widget-content ui-dialog-buttonpane">
              <div class="ui-dialog-buttonset">
                <a class="button" onclick="createGroup();">Erstellen</a>
              </div>
          </div>
      </div>
  </div>
</script>
