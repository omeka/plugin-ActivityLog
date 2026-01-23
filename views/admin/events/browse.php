<?php
queue_js_file('activitylog');
queue_css_file('activitylog');

$searchFilters = [];
foreach ($_GET as $key => $value) {
    switch ($key) {
        case 'id':
            $searchFilters[] = sprintf('Event ID: %s', $value);
            break;
        case 'event':
            $searchFilters[] = sprintf('Event: %s', $value);
            break;
        case 'resource':
            $searchFilters[] = sprintf('Resource: %s', $value);
            break;
        case 'resource_identifier':
            $searchFilters[] = sprintf('Resource ID: %s', $value);
            break;
        case 'user_id':
            $user = get_db()->getTable('User')->find($value);
            $searchFilters[] = sprintf('User: %s', $user->username);
            break;
        case 'user_role':
            $searchFilters[] = sprintf('User role: %s', $value);
            break;
        case 'ip':
            $searchFilters[] = sprintf('IP: %s', $value);
            break;
        case 'from':
            $searchFilters[] = sprintf('From: %s', $value);
            break;
        case 'before':
            $searchFilters[] = sprintf('Before: %s', $value);
            break;
        }
}

$sortLinks = [
    __('ID') => 'id',
    __('Messages') => null,
    __('Date') => 'timestamp',
    __('User') => null,
    __('IP') => null,
    __('Event Name') => 'event',
    __('Resource') => 'resource',
];

$table = get_db()->getTable('ActivityLogEvent');

echo head([
    'title' => __('Activity Log Events') . ' ' . __('(%s total)', $total_results),
    'bodyclass' => 'activity-log browse',
]);
?>

<?php if ($searchFilters): ?>
<div id="search-filters">
    <ul>
        <?php foreach ($searchFilters as $searchFilter): ?>
        <li><?php echo html_escape($searchFilter); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php echo pagination_links(['attributes' => ['aria-label' => __('Top pagination')]]); ?>

<div role="group" id="event-filter-controls" aria-label="<?php echo __('Filter controls'); ?>">
    <?php echo $this->formButton(null, __('View filters'), ['id' => 'open-event-filters', 'class' => 'blue button']); ?>
    <dialog id="event-filter-dialog" aria-labelledby="event-filter-dialog-heading">
        <div class="modal-header">
            <h2 id="event-filter-dialog-heading"><?php echo __('Filter events'); ?></h2>
            <button type="button" class="modal-close" aria-label="<?php echo __('Close'); ?>"></button>
        </div>
        <form id="event-filter-form">
            <label>
                <span class="label-text"><?php echo __('Event ID'); ?></span>
                <?php echo $this->formInput('id', $_GET['id'] ?? null); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('Event name'); ?></span>
                <?php echo $this->formSelect('event', $_GET['event'] ?? null, [], $table->getEventValueOptions()); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('Resource'); ?></span>
                <?php echo $this->formSelect('resource', $_GET['resource'] ?? null, [], $table->getResourceValueOptions()); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('Resource ID'); ?></span>
                <?php echo $this->formInput('resource_identifier', $_GET['resource_identifier'] ?? null); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('User'); ?></span>
                <?php echo $this->formSelect('user_id', $_GET['user_id'] ?? null, [], $table->getUserValueOptions()); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('User role'); ?></span>
                <?php echo $this->formSelect('user_role', $_GET['user_role'] ?? null, [], $table->getUserRoleValueOptions()); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('IP'); ?></span>
                <?php echo $this->formInput('ip', $_GET['ip'] ?? null); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('From'); ?></span>
                <?php echo $this->formInput('from', $_GET['from'] ?? null, ['type' => 'date']); ?>
            </label>
            <label>
                <span class="label-text"><?php echo __('Before'); ?></span>
                <?php echo $this->formInput('before', $_GET['before'] ?? null, ['type' => 'date']); ?>
            </label>
            <?php echo $this->formButton(null, __('Apply Filters'), ['type' => 'submit', 'class' => 'blue button']); ?>
             <a class="blue button" href="<?php echo html_escape(current_url()); ?>"><?php echo __('Clear Filters'); ?></a>
        </form>
    </dialog>
</div>

<?php if ($total_results): ?>

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

<?php else: ?>

<h2><?php echo __('No events found.'); ?></h2>

<?php endif; ?>

<?php echo pagination_links(['attributes' => ['aria-label' => __('Bottom pagination')]]); ?>

<?php echo foot(); ?>
