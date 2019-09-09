<table class="default">
    <caption>Veranstaltungen</caption>

    <tbody>
        <? foreach ($courses as $course_id => $course) : ?>
        <tr>
            <td>
                <a class="link-intern" target="_blank"
                    href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor?cid=' . $course_id) ?>"
                >
                    <?= htmlReady($course) ?>
                </a>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
