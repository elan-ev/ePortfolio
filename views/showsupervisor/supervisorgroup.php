<h1>"<?php echo $groupName; ?>" - Supervisoren verwalten</h1>

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
           <?php $userInfo = UserModel::getUser($user[user_id]);?>
         <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $userInfo['username']) ?>" >
                        <?= Avatar::getAvatar($user['user_id'], $userInfo['username'])->getImageTag(Avatar::SMALL,
                                array('style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>
                        <?= htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']) ?>         
                    </a>

      </td>
      <td></td>
      <td style="text-align:center;">
        <a onclick="return deleteUserFromGroup('<?php echo $groupId ?>', '<?php echo $user[user_id] ?>', this)"><?php echo  Icon::create('trash', 'clickable'); ?></a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php echo $mp; ?>


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
</script>
