<?php
echo head(['title' => __('Activity Log Events'), 'bodyclass' => 'browse']);
$sortLinks = [
    __('ID') => 'id',
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
            <?php
            $user = $event->User;
            $record = $event->Record;
            $resourceText = [$event->resource];
            if ($event->resource_identifier) {
                $resourceText[] = sprintf(__('ID: %s'), $event->resource_identifier);
            }
            if ($record) {
                $resourceText[] = link_to($record, null, __('View resource'));
            }
            ?>
            <tr>
                <td><?php echo $event->id; ?></td>
                <td><?php echo $event->DateTime->format('Y-m-d<\b\r>H:i:s.v'); ?></td>
                <td><?php echo $user
                    ? sprintf('%s<br>%s', link_to($user, 'edit', $user->username, ['class'=>'edit']), $user->role)
                    : sprintf('[%s]', __('unknown')); ?></td>
                <td><?php echo $event->ip; ?></td>
                <td><?php echo $event->event; ?></td>
                <td><?php echo implode('<br>', $resourceText); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo pagination_links(['attributes' => ['aria-label' => __('Bottom pagination')]]); ?>

<?php else: ?>

<?php echo __('No events found.'); ?>

<?php endif; ?>
