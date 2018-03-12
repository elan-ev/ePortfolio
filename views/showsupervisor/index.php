<? use Studip\LinkButton; ?>

<h1>Supervisionsgruppe "<?php echo $courseName; ?>"</h1>


<div>

      <!-- <select class="" id="tempselector" name="template">
        <?php  $templates = Eportfoliomodel::getPortfolioVorlagen(); ?>
        <?php foreach ($templates as $key => $value):?>
          <option value="<?php echo $value[id] ?>"><?php echo $value[temp_name] ?></option>
        <?php endforeach; ?>
      </select>
      <?= \Studip\Button::create('Hinzufügen', 'button', array('type' => 'button', 'onclick' => 'addTemp()')); ?> -->

      <div id="wrapper_table_tamplates" style="margin-top: 30px;">
        <h4>Portfoliovorlage hinzufügen</h4>

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
            <?php $temps = Eportfoliomodel::getPortfolioVorlagen();
              foreach ($temps as $key):?>
              <?php $thisPortfolio = new Seminar($key); ?>
              <?php $eportfolio = new eportfolio($key); ?>
              <?php if (ShowsupervisorController::checkTemplate($id, $key) == false): ?>
                <tr>
                  <td><?php echo $thisPortfolio->getName(); ?></td>
                  <td><?php echo $eportfolio->getBeschreibung(); ?></td>
                  <td style="text-align: center;">

                      <a href="<?php echo URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $key)); ?>"><?php echo Icon::create('edit', 'clickable', ['title' => sprintf(_('Portfolio-Vorlage bearbeiten.'))]) ?></a>
                      <?php if($groupList): ?>
                      <a data-dialog="reload-on-close" href="<?= PluginEngine::getLink($this->plugin, array(), 'showsupervisor/createportfolio/' . $key . '/' . $id) ?>">
                        <? $params = tooltip2(_("Portfolio-Vorlage an Gruppenmitglieder verteilen.")); ?>
                        <? $params['style'] = 'cursor: pointer'; ?>
                        <?= Icon::create('add', 'clickable')->asImg(20, $params) ?>
                       </a>
                       <?php endif ?>
                  </td>
                </tr>
              <?php endif; ?>

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

  <?php
    if (empty($groupTemplates[0])):
  ?>

    <h4>Gruppenmitglieder</h4>

    <?php if (!$groupList):?>
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
                <a href="<?= $controller->url_for(sprintf('showsupervisor/deleteUserFromGroup/%s/%s', $user, $id)) ?>">
                    <?=Icon::create('trash', 'clickable', ['title' => sprintf(_('Nutzer aus Gruppe austragen'))])?>
                </a>
          </tr>
        <?php endforeach; ?>
      </table>

    <?php endif; ?>

    <?php else: ?>

    <!-- Nav tabs -->
    <div id="vorlagen-tabs">
    <ul>
      <?php foreach ($templistid as $key => $value): ?>
        <?php $template = new Seminar($value);?>
        <li><a href="#tabs-<?= $value; ?>"><?= $template->getName(); ?></a></li>
      <?php endforeach; ?>
    </ul>
    <!-- Tab panes -->

    <!-- für alle verteilten Vorlagen: -->
      <?php foreach ($templistid as $key => $value): ?>
        <?php $tempid = $value ?>
        <div id="tabs-<?= $value; ?>">
          <table class="default">
            <tr>
              <th style="width: 200px;border-bottom: 1px solid;">Name</th>
              <?php
                // hole die Kapitel der verteilten Vorlagen
                $q = ShowsupervisorController::getChapters($value);
                foreach ($q as $key): ?>
                  <th style="width: 100px; border-bottom: 1px solid;"><?php print_r($key['title']); ?></th>
              <?php endforeach; ?>
            </tr>
            <!-- für alle Gruppenteilnehmer: -->
            <?php foreach ($groupList as $key):?>
              <tr>
                <td style="text-align: left;">
                  <img style="border-radius: 30px; width: 21px; border: 1px solid #28497c;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/<?php echo $key; ?>_small.png" onError="defaultImg(this);">
                  <?php $supervisor = UserModel::getUser($key);
                  $userid = $key;
                      echo $supervisor[Vorname].' '.$supervisor[Nachname];
                   ?>
                </td>
                <?php
                // hole das zugehörige Portfolio des Teilnehmers
                $query = "SELECT Seminar_id FROM eportfolio WHERE owner_id = :key AND template_id = :tempid AND group_id = :groupid";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array(':key'=> $key, ':tempid'=> $tempid, ':groupid'=> $groupid));
                $getsemid = $statement->fetchAll()[0][0];
                ?>

                <?php
                // wozu ist das hier??
                $query = "SELECT templateStatus FROM eportfolio WHERE Seminar_id = :semid";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array(':semid'=> $getsemid));
                $status = $statement->fetchAll()[0][0];

                // hole alle Kapitel des Portfolios des Teilnemers
                $q = ShowsupervisorController::getChapters($getsemid);

              //Übergangslösung Kapitel 1 & Kapitel 2 müssen noch entfernt werden
              //nset($q[0]);
              //unset($q[1]);

                foreach ($q as $value): ?>

                    <td>
                        <?php
                        $idNew = $value[id];
                        $hasAccess = EportfolioFreigabe::hasAccess($supervisorGroupId, $getsemid, $idNew); ?>

                        <?php if($hasAccess):?>
                            <?php $link = URLHelper::getLink("plugins.php/courseware/courseware", array('cid' => $getsemid , 'selected' => $idNew));?>
                            <a class='freigabe-link' href="<?php echo $link; ?>">
                              <?= Icon::create('accept', 'clickable'); ?>
                            </a>

                            <?php if (ShowsupervisorController::checkSupervisorNotiz($idNew) == true): ?>
                            <a class='freigabe-link' href="<?php echo URLHelper::getLink("plugins.php/courseware/courseware", array('cid' => $getsemid , 'selected' => $idNew)) ?>">
                              <?= Icon::create('file', 'clickable'); ?>
                            </a>
                            <?php endif; ?>

                            <?php if (ShowsupervisorController::checkSupervisorFeedback($idNew) == true): ?>
                            <a class='freigabe-link' href="<?php echo URLHelper::getLink("plugins.php/courseware/courseware", array('cid' => $getsemid , 'selected' => $idNew)) ?>">
                              <?= Icon::create('forum', 'clickable'); ?>
                            </a
                            <?php endif; ?>

                        <?php endif; ?>

                    </td>


                <?php endforeach; ?>

              </tr>
            <?php endforeach; ?>
          </table>

          <!-- <button type="button" name="button" onclick="deletetemplate(<?php echo $tempid; ?>)">Vorlage fr diese Gruppe lschen</button> -->
          <!--<?= \Studip\Button::create('Vorlage für diese Gruppe löschen', 'button', array('type' => 'button', 'onclick' => 'deletetemplate('.$tempid.')')); ?>-->

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

  <?php echo MessageBox::info('Aktuell haben Sie noch keine Gruppen erstellt. Bitte erstellen Sie zunächst ein Gruppe um mit der Verwaltung fortzufahren.'); ?>

</div>

<?php endif; ?>

<?php
  $mp = MultiPersonSearch::get('eindeutige_id')
    ->setLinkText(_('Personen hinzufügen'))
    ->setTitle(_('Personen zur Gruppe hinzufügen'))
    ->setSearchObject(new StandardSearch('user_id'))
    ->setJSFunctionOnSubmit('addUserToGroup')
    ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor', array('cid' => $groupid, 'action' => 'addUsersToGroup')))
    ->render();
 ?>

<?php if (empty($groupTemplates)):?>
   <a href="<?php echo URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id'); ?>" class="multi_person_search_link" data-dialog="width=720;height=460;id=mp-search" data-dialogname="eindeutige_id" title="Personen zur Gruppe hinzufügen" data-js-form="<?php echo URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id'); ?>">
     <?= \Studip\Button::create('Personen hinzufügen', 'klickMichButton', array('data-dialogname' => 'eindeutige_id', 'data-js-form' => URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id'))); ?>
   </a>
<?php endif; ?>

<a href="<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/delete', array('id' => $cid)); ?>">
  <?= \Studip\Button::create('Gruppe löschen', 'klickMichButton', array('data-dialogname' => 'eindeutige_id', 'data-js-form' => URLHelper::getLink('dispatch.php/multipersonsearch/js_form/eindeutige_id'))); ?>
</a>

<!-- Legende -->
<div class="legend">
  <ul>
    <li><?php echo  Icon::create('accept', 'clickable'); ?>  Kapitel/Implus freigeschaltet</li>
    <li><?php echo  Icon::create('accept+new', 'clickable'); ?></i>  Kapitel freigeschaltet und Änderungen seit ich das letzte mal reingeschaut habe</li>
    <li><?php echo  Icon::create('file', 'clickable'); ?>  Supervisionsanliegen freigeschaltet</li>
    <li><?php echo  Icon::create('forum', 'clickable'); ?>  Resonanz gegeben</li>
  </ul>
</div>



<script type="text/javascript">

$( function() {
    $( "#vorlagen-tabs" ).tabs();
} );

$('#myModal').on('shown.bs.modal', function () {
$('#myInput').focus()
})

if ( $('#table_templates').find("td").length === 0 ) {
    $('#wrapper_table_tamplates').css('display', 'none');
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


function closeModal(){
  $('.modal-area').empty();
}



</script>
