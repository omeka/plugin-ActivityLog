<?php
class ActivityLogPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'define_acl',
        'after_save_record',
        'after_delete_record',
    );

    protected $_filters = array(
        'admin_navigation_main',
        'activity_log_event_messages',
    );

    public function hookInstall()
    {
        $db = get_db();
        $db->query(<<<SQL
CREATE TABLE `{$db->prefix}activity_log_events` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED DEFAULT NULL,
  `timestamp` double NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `resource` varchar(255) DEFAULT NULL,
  `resource_identifier` varchar(255) DEFAULT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL
        );
    }

    public function hookUninstall()
    {
        $db = get_db();
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}activity_log_events`");
    }

    public function hookConfigForm($args)
    {
        $view = $args['view'];
        include 'config_form.php';
    }

    public function hookConfig($args)
    {
        $deleteBefore = $_POST['delete_before'];
        if (isset($deleteBefore) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $deleteBefore)) {
            // Delete event rows before delete_before.
            $db = get_db();
            $table = $db->getTableName('ActivityLogEvent');
            $query = "DELETE FROM `$table` WHERE `timestamp` < ?";
            $dateTime = new DateTime(
                $deleteBefore,
                new DateTimeZone(date_default_timezone_get())
            );
            $db->getAdapter()->query($query, [$dateTime->getTimestamp()]);
        }
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('ActivityLog_Events');
    }

    public function hookAfterSaveRecord($args)
    {
        $record = $args['record'];
        $recordName = get_class($record);
        $insert = $args['insert'];

        activity_log_log_event(
            sprintf('after_%s_record', $insert ? 'insert' : 'update'),
            $recordName,
            $record->id,
            json_encode($record)
        );
    }

    public function hookAfterDeleteRecord($args)
    {
        $record = $args['record'];
        $recordName = get_class($record);

        if ('ActivityLog' === $record->name) {
            // Do not attempt to log after ActivityLog is uninstalled.
            return;
        }

        activity_log_log_event(
            'after_delete_record',
            $recordName,
            $record->id,
            json_encode($record)
        );
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = [
            'label' => __('Activity Log'),
            'uri' => url('activity-log/events'),
            'resource' => ('ActivityLog_Events'),
        ];
        return $nav;
    }

    public function filterActivityLogEventMessages($messages, $args)
    {
        $event = $args['event'];

        $eventNames = ['after_insert_record', 'after_update_record', 'after_delete_record'];
        if (!in_array($event->event, $eventNames)) {
            return $messages;
        }

        // This filter will only add event messages for records that were saved
        // or deleted via Omeka_Record_AbstractRecord.
        if ('after_insert_record' === $event->event) {
            $messages[] = sprintf(__('Created a "%s" record'), $event->resource);
        } else if ('after_update_record' === $event->event) {
            $messages[] = sprintf(__('Updated a "%s" record'), $event->resource);
        } else if ('after_delete_record' === $event->event) {
            $messages[] = sprintf(__('Deleted a "%s" record'), $event->resource);
        }

        $messages[] = sprintf(__('ID: %s'), $event->resource_identifier);

        $record = $event->Record;
        if ($record) {
            switch ($event->resource) {
                case 'Item':
                case 'Collection':
                case 'File':
                case 'ItemType':
                case 'Exhibit':
                case 'ExhibitPage':
                    $messages[] = link_to($record, null, __('View record'));
                    break;
            }
        }

        return $messages;
    }
}

/**
 * Log an event.
 *
 * @param string $event The event name (typically the related hook name)
 * @param string $resource The resource name
 * @param string $resource_identifier The resource identifier (if any)
 * @param string $data The data used to perform the event (if any)
 */
function activity_log_log_event($event, $resource, $resource_identifier, $data)
{
    $db = get_db();
    $eventId = $db->insert('ActivityLogEvents', [
        'user_id' => current_user()->id,
        'timestamp' => microtime(true),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'event' => $event,
        'resource' => $resource,
        'resource_identifier' => $resource_identifier,
        'data' => $data,
    ]);
    return $eventId;
}
