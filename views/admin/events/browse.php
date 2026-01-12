<?php
echo head(['title' => __('Activity Log Events'), 'bodyclass' => 'browse']);
$sortLinks = [
    __('ID') => 'id',
    __('Messages') => null,
    __('Date') => 'timestamp',
    __('User') => null,
    __('IP') => null,
    __('Event Name') => 'event',
    __('Resource') => 'resource',
];
?>

<?php if ($total_results): ?>

<?php echo pagination_links(['attributes' => ['aria-label' => __('Top pagination')]]); ?>
<div class="table-responsive">
    <table id="events">
        <thead>
            <tr>
                <?php echo browse_sort_links($sortLinks, ['link_tag' => 'th scope="col"', 'list_tag' => '']); ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (loop('ActivityLogEvent') as $event): ?>
            <tr>
                <td><?php echo link_to($event, null, $event->id); ?></td>
                <td>
                    <ul>
                        <?php foreach ($event->Messages as $message): ?>
                        <li><?php echo $message; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
                <td><?php echo $event->DateTime->format('Y-m-d<\b\r>H:i:s.v'); ?></td>
                <td><?php
                    $user = $event->User;
                    echo $user
                        ? sprintf('%s<br>%s', link_to($user, 'edit', $user->username, ['class'=>'edit']), $user->role)
                        : sprintf('[%s]', __('unknown')); ?>
                </td>
                <td><?php echo $event->ip; ?></td>
                <td><?php echo $event->event; ?></td>
                <td><?php echo $event->resource; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo pagination_links(['attributes' => ['aria-label' => __('Bottom pagination')]]); ?>

<?php else: ?>

<?php echo __('No events found.'); ?>

<?php endif; ?>
