<h1><?php echo $title; ?></h1>

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
  <?php foreach ($usersOfGroup  as $user):?>
    <tr>
      <td>
        <img style="border-radius: 30px; width: 21px; border: 1px solid #28497c;" src="<?php echo $GLOBALS[DYNAMIC_CONTENT_URL];?>/user/<?php echo $user->user_id; ?>_small.png" onError="defaultImg(this);">
        <?php $userInfo = User::find($user->user_id);?><?php echo $userInfo['Vorname']." ".$userInfo['Nachname']; ?>
      </td>
      <td></td>
      <td style="text-align:center;">
        <a onclick="deleteUserFromGroup('<?php echo $groupId ?>', '<?php echo $user->user_id ?>', this)"><?php echo  Icon::create('trash', 'clickable'); ?></a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php echo $mp; ?>
<hr>
<?php $url = URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/deleteGroup', array('cid' => $linkId));?>
<?= \Studip\LinkButton::create('Löschen',  $url); ?>

<div id="modalNewSupervisorGroup" class="modaloverlay" style="display: none;">
   <div class="create-question-dialog ui-widget-content ui-dialog studip-confirmation">
       <div style="background-color: #28497c;" class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
           <span style="color:#fff;">Neue Supervisorengruppe</span>
           <a style="color:#fff;" onclick="hideModalNewSupervisorGroupAction();" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close">
               <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
               <span class="ui-button-text">Schliessen</span>
           </a>
       </div>
       <div style="background:none;padding: 10px;" class="content ui-widget-content ui-dialog-content studip-confirmation">
           <form id="createGroupForm">
             <label>
               <span class="required">Name</span>
               <input style="width: 100%;" type="text" name="name" id="groupName" maxlength="254" value="" required="" aria-required="true" aria-invalid="true">
             </label>
         </form>
       </div>
       <div class="buttons ui-widget-content ui-dialog-buttonpane">
           <div class="ui-dialog-buttonset">
             <a class="button" onclick="createNewSupervisorGroup();">Erstellen</a>
           </div>
       </div>
   </div>
</div>

<script type="text/javascript">
  function deleteUserFromGroup(groupId, userId, obj){
    $.ajax({
      type: "POST",
      url: "<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/deleteUser');?>",
      data: {
        groupId: groupId,
        userId: userId
      },
      success:function(data){
        $(obj).parents('td').fadeOut();
      }
    });
  }

  function showModalNewSupervisorGroupAction(){
    $('#modalNewSupervisorGroup').css('display', 'grid');
  }

  function hideModalNewSupervisorGroupAction(){
    $('#modalNewSupervisorGroup').css('display', 'none');
  }

  function createNewSupervisorGroup(){
    var name = $('#groupName').val();
    $.ajax({
      type: "POST",
      url: "<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/newGroup'); ?>",
      data: {
        groupName: name
      },
      success: function(data){
        location.reload();
      }
    });
  }

</script>
