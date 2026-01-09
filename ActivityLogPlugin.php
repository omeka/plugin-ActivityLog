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
        $post = $args['post'];
        $insert = $args['insert'];

        if ($this->recordIsExcluded($record)) {
            return;
        }

        // Resolve to a specific event name. This allows us to use the generic
        // "after_save_record" hook to unambiguously log insert and update
        // events for all records.
        $event = sprintf(
            '%s_%s',
            $insert ? 'insert' : 'update',
            Inflector::underscore(get_class($record))
        );

        activity_log_log_event(
            $event,
            get_class($record),
            $record->id,
            json_encode($post)
        );
    }

    public function hookAfterDeleteRecord($args)
    {
        $record = $args['record'];

        if ($this->recordIsExcluded($record)) {
            return;
        }

        // Resolve to a specific event name. This allows us to use the generic
        // "after_delete_record" hook to unambiguously log delete events for all
        // records.
        $event = sprintf('delete_%s', Inflector::underscore(get_class($record)));

        activity_log_log_event(
            $event,
            get_class($record),
            $record->id,
            null
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

    /**
     * Exclude this record from the log?
     *
     * @param Omeka_Record_AbstractRecord $record
     * @return bool
     */
    protected function recordIsExcluded($record)
    {
        $excludedRecords = ['ElementText', 'SearchText'];
        return in_array(get_class($record), $excludedRecords);
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
