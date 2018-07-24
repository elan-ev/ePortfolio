<div class="row">
  <div class="col-sm-2 member-avatar">
    <?= Avatar::getAvatar($user, $userInfo['username'])->getImageTag(Avatar::SMALL,array('style' => 'margin-right: 0px; border-radius: 75px; height: 75px; width: 75px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>
  </div>
  <div class="col-sm-5">
      <div class="member-name-detail">
        Marcel Kipp
      </div>
      <div class="member-subname">
        Status: <span class="member-status-label">.</span> <br>
        Studiengang: Medieninformatik <br>
        Portfoliogruppe: Testgruppe<br>
        Letzte �nnderung am: 23.05.2018
      </div>
  </div>
  <div class="col-sm-5">
    <div class="row row member-footer-box-detail">
      <div class="col-sm-4">
        <div class="member-footer-box-big-detail">
          12 / 24
        </div>
        <div class="member-footer-box-head">
          freigegeben
        </div>
      </div>
      <div class="col-sm-4">
        <div class="member-footer-box-big-detail">
          67 %
        </div>
        <div class="member-footer-box-head">
          bearbeitet
        </div>
      </div>
      <div class="col-sm-4">
        <div class="member-footer-box-big-detail">
          5
        </div>
        <div class="member-footer-box-head">
          Notizen
        </div>
      </div>
    </div>
  </div>
</div>

<div class="member-contant-detail">

  <div class="row member-containt-head-detail">
    <div class="col-sm-4">Kapitelname</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2">Freigegeben</div>
        <div class="col-sm-2">Resonanz</div>
        <div class="col-sm-2">Anliegen</div>
        <div class="col">Aktionen</div>
      </div>
    </div>
  </div>

  <div class="row member-content-single-line">
    <div class="col-sm-4 member-content-single-line-ober">Kapitel</div>
    <div class="col-sm-8">
      <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-2"></div>
        <div class="col-sm-2"></div>
        <div class="col member-aktionen-detail">
          <a href="#">Anschauen</a>
          <a href="#">Feedback geben</a>
        </div>
      </div>
    </div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'clickable'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div>
    </div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'clickable'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div></div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'clickable'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div>
    </div>
  </div>


  <div class="row member-content-single-line">
    <div class="col-sm-4 member-content-single-line-ober">Kapitel <span class="label-selber">Eigenes</span></div>
    <div class="col-sm-8">
      <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-2"></div>
        <div class="col-sm-2"></div>
        <div class="col member-aktionen-detail">
          <a href="#">Anschauen</a>
          <a href="#">Feedback geben</a>
        </div>
      </div>
    </div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div>
    </div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div>
    </div>
  </div>

  <div class="row member-content-single-line">
    <div class="col-sm-4 member-content-single-line-ober">Kapitel</div>
    <div class="col-sm-8">
      <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-2"></div>
        <div class="col-sm-2"></div>
        <div class="col member-aktionen-detail">
          <a href="#">Anschauen</a>
          <a href="#">Feedback geben</a>
        </div>
      </div>
    </div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'clickable'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div>
    </div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'clickable'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div></div>

    <div class="col-sm-4 member-content-unterkapitel">Unterkapitel</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2"><?php echo  Icon::create('accept', 'clickable'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('forum', 'inactive'); ?></div>
        <div class="col-sm-2"><?php echo  Icon::create('file', 'clickable'); ?> </div>
      </div>
    </div>
  </div>

</div>
