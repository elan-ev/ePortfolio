<? use Studip\LinkButton; ?>


<div class="row">

  <div class="col-md-12">

    <div class="jumbotron" style="border-radius: 10px;">
      <div class="container" style="padding: 0 50px;">

        <h2>
          <?= Avatar::getAvatar($user->id, $userInfo['username'])->getImageTag(Avatar::MEDIUM,
                                array('style' => 'margin-right: 5px;border-radius: 35px; height:36px; width:36px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname'])));  ?>
          <span id="headline_uebersicht"></span>
        </h2>

        <p>Hier finden Sie alle ePortfolios, die Sie angelegt haben oder die andere f&uuml;r Sie freigegeben haben.</p>
        <!-- <p><?= \Studip\Button::create('Mehr Informationen'); ?></p> -->
      </div>
    </div>

  </div>

</div>



<?php if ($isDozent):?>
<div class="row">
  <div class="col-md-12">
    <table class="default">
      <caption>Portfolio Vorlagen
       <span class='actions'> <a data-dialog="size=auto;reload-on-close" href="<?= PluginEngine::getLink($this->plugin, array(), 'show/createvorlage') ?>">      
            <? $params = tooltip2(_("Neue Vorlage erstellen")); ?>
                    <? $params['style'] = 'cursor: pointer'; ?>
                    <?= Icon::create('add', 'clickable')->asImg(20, $params) ?>
       </span>
        </a></caption>
      <colgroup>
        <col width="30%">
        <col width="60%">

      </colgroup>
      <thead>
        <tr class="sortable">
          <th>Name</th>
          <th>Beschreibung</th>
          <th>Aktionen</th>

        </tr>
      </thead>

      <tbody>
        <?php $temps = Eportfoliomodel::getPortfolioVorlagen();

          foreach ($temps as $key):?>

          <?php $thisPortfolio = new Seminar($key); ?>

          <tr>
            <td><?php echo $thisPortfolio->getName(); ?></td>
            <td><?php echo ShowController::getCourseBeschreibung($key); ?></td>
            <td style="text-align: center;"><a href="<?php echo URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $key)); ?>" title='Portfolio-Vorlage bearbeiten'><?php echo Icon::create('edit', 'clickable') ?></a></td>
          </tr>

        <?php endforeach; ?>
      </tbody>
    </table>
       

  </div>


</div>
<?php endif; ?>

<div class="row">
  <div class="col-md-12">

    <?php ?>

    <table class="default">
      <caption>Meine Portfolios
      <span class='actions'> 
          <a data-dialog="size=auto;reload-on-close" href="<?= PluginEngine::getLink($this->plugin, array(), 'show/createportfolio') ?>">
                    <? $params = tooltip2(_("Neues Portfolio erstellen")); ?>
                    <? $params['style'] = 'cursor: pointer'; ?>
                    <?= Icon::create('add', 'clickable')->asImg(20, $params) ?>
        </a>
       </span>
      </caption>
      <colgroup>
        <col width="30%">
        <col width="50%">
        <col width="10%" style="text-align: center;">
        <col width="10%" style="text-align: center;">
      </colgroup>
      <thead>
        <tr class="sortable">
          <th>Portfolio-Name</th>
          <th>Beschreibung</th>
          <th style="text-align: center;">Freigaben</th>
          <th style="text-align: center;">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        <?php $countPortfolios = 0; ?>
        <?php $myportfolios = ShowController::getMyPortfolios(); ?>
        <?php foreach ($myportfolios as $portfolio): ?>
          <?php $thisPortfolio = new Seminar($portfolio);
                $countPortfolios++; ?>
          <tr class=''>
            <td><a href="<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('cid' => $portfolio)); ?>"><?php echo $thisPortfolio->getName(); ?></a></td>
            <td><?php echo ShowController::getCourseBeschreibung($portfolio); ?></td>
            <td style="text-align: center;"><?php echo ShowController::countViewer($portfolio); ?></td>
            <td style="text-align: center;"><a href="<?php echo URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $portfolio)); ?>" title='Portfolio bearbeiten'><?php echo Icon::create('edit', 'clickable') ?></a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      
      <script type="text/javascript">

        function PortfolioHeadline(i) {
          var one = "Mein Portfolio";
          var two = "Meine Portfolios"

          if (i <= 1) {
            $('#headline_uebersicht').text('Mein Portfolio');
          } else {
            $('#headline_uebersicht').text('Meine Portfolios');
          }
        }

        PortfolioHeadline(<?php echo $countPortfolios; ?>);

      </script>

    </table>
  </div>
</div>



<div class="row">
  <div class="col-md-12">
    <table  class="default">
      <caption>F&uuml;r mich freigegebene Portfolios</caption>
      <colgroup>
        <col width="30%">
        <col width="60%">
        <col width="10%">
      </colgroup>
      <thead>
        <tr class="sortable">
          <th>Portfolio-Name</th>
          <th>Beschreibung</th>
          <th>Besitzer</th>
        </tr>
      </thead>
      <tbody>
      <?php $myAccess = ShowController::getAccessPortfolio(); ?>
      <?php foreach ($myAccess as $portfolio): ?>
        <?php $thisPortfolio = new Seminar($portfolio); ?>
        <tr class='insert_tr'>
          <td><a href='<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('cid' => $portfolio)); ?>'><?php echo $thisPortfolio->getName(); ?></a></td>
          <td></td>
          <td>
            <?php
              print_r(ShowController::getOwnerName($thisPortfolio->getId()));
             ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>


