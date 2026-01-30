<?php
echo head(['title' => __('Activity Log Event'), 'bodyclass' => 'show']);
$user = $activity_log_event->User;
?>

<dl>
    <dt><?php echo __('ID'); ?></dt>
    <dd><?php echo $activity_log_event->id; ?></dd>
    <dt><?php echo __('Messages'); ?></dt>
    <dd>
        <ul>
            <?php foreach ($activity_log_event->Messages as $message): ?>
            <li><?php echo $message; ?></li>
            <?php endforeach; ?>
        </ul>
    </dd>
    <dt><?php echo __('Date'); ?></dt>
    <dd><?php echo $activity_log_event->DateTime->format('Y-m-d<\b\r>H:i:s.v'); ?></dd>
    <dt><?php echo __('User'); ?></dt>
    <dd><?php echo $user
        ? sprintf('%s<br>%s', link_to($user, 'edit', $user->username, ['class'=>'edit']), $user->role)
        : sprintf('[%s]', __('unknown'));
    ?></dd>
    <dt><?php echo __('IP'); ?></dt>
    <dd><?php echo $activity_log_event->ip; ?></dd>
    <dt><?php echo __('Event name'); ?></dt>
    <dd><?php echo $activity_log_event->event; ?></dd>
    <dt><?php echo __('Resource'); ?></dt>
    <dd><?php echo $activity_log_event->resource; ?></dd>
    <dt><?php echo __('Resource identifier'); ?></dt>
    <dd><?php echo $activity_log_event->resource_identifier; ?></dd>
</dl>
<h3 id="event-data"><?php echo __('Data'); ?></h3>
<pre style="font-size: 12px;"><?php echo htmlspecialchars(json_encode($activity_log_event->Data, JSON_PRETTY_PRINT)); ?></pre>

<?php echo foot(); ?>
