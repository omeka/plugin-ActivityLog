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

<form id="event-filter-form">
    <?php
    $table = get_db()->getTable('ActivityLogEvent');
    echo $this->formInput('id', $_GET['id'] ?? null, ['placeholder' => __('Enter an event ID')]);
    echo $this->formSelect('event', $_GET['event'] ?? null, [], $table->getEventValueOptions());
    echo $this->formSelect('resource', $_GET['resource'] ?? null, [], $table->getResourceValueOptions());
    echo $this->formInput('resource_identifier', $_GET['resource_identifier'] ?? null, ['placeholder' => __('Enter a resource ID')]);
    echo $this->formSelect('user_id', $_GET['user_id'] ?? null, [], $table->getUserValueOptions());
    echo $this->formSelect('user_role', $_GET['user_role'] ?? null, [], $table->getUserRoleValueOptions());
    echo $this->formInput('ip', $_GET['ip'] ?? null, ['placeholder' => __('Enter an IP')]);
    echo $this->formInput('from', $_GET['from'] ?? null, ['type' => 'date']);
    echo $this->formInput('before', $_GET['before'] ?? null, ['type' => 'date']);
    echo $this->formButton(null, __('Apply Filters'), ['type' => 'submit']);
    ?>
    <a class="button" href="<?php echo html_escape(current_url()); ?>"><?php echo __('Clear Filters'); ?></a>
</form>

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
                <td><?php echo $event->id; ?></td>
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

<script>
// Do not submit filters with empty values because they would always return no results.
document.getElementById('event-filter-form').addEventListener('submit', function(e) {
    for (const control of this.elements) {
        if (control.value === '') {
            control.disabled = true;
        }
    }
});
</script>
