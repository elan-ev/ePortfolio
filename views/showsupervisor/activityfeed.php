<div class="activity-feed-container">
    <h1>
        <?= _('Neue Aktivitäten') ?>
    </h1>
    <?= $this->render_partial('showsupervisor/_activities.php', [
        'activities' => array_filter($activities, function($entry) {
                return $entry->is_new;
            })
        ]); ?>

    <h1>
        <?= _('Alte Aktivitäten') ?>
    </h1>

    <?= $this->render_partial('showsupervisor/_activities.php', [
        'activities' => array_filter($activities, function($entry) {
                return !$entry->is_new;
            })
        ]); ?>

    <?= object_set_visit(Context::getId(), 'sem'); ?>
</div>
