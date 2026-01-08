<?php
echo head(['title' => __('Activity Log Events'), 'bodyclass' => 'browse']);
?>

<?php if ($total_results): ?>

<?php echo pagination_links(['attributes' => ['aria-label' => __('Top pagination')]]); ?>
<div class="table-responsive">
    <table id="events">
        <thead>
            <tr>
                <th><?php echo __('ID'); ?></th>
                <th><?php echo __('Date'); ?></th>
                <th><?php echo __('User'); ?></th>
                <th><?php echo __('IP'); ?></th>
                <th><?php echo __('Event Name'); ?></th>
                <th><?php echo __('Resource'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (loop('ActivityLogEvent') as $event): ?>
            <tr>
                <td><?php echo $event->id; ?></td>
                <td><?php echo $event->timestamp; ?></td>
                <td><?php echo $event->user_id; ?></td>
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
