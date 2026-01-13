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
        'activity_log_record_data',
        'activity_log_event_messages',
    );

    protected $_excludeRecords = [
        'Element',
        'ElementText',
        'ItemTypesElements',
        'SearchText',
    ];

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

        if (in_array($recordName, $this->_excludeRecords)) {
            // Exclude certain records from the log.
            return;
        }

        // Set the POST as the event data. If there is no POST, attempt to get
        // the event data via the "activity_log_record_data" filter.
        $data = $args['post'] ?: apply_filters('activity_log_record_data', null, ['record' => $record]);

        activity_log_log_event(
            sprintf('after_%s_record', $insert ? 'insert' : 'update'),
            $recordName,
            $record->id,
            json_encode($data)
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
        if (in_array($recordName, $this->_excludeRecords)) {
            // Exclude certain records from the log.
            return;
        }

        // Attempt to get the event data via the "activity_log_record_data"
        // filter.
        $data = apply_filters('activity_log_record_data', null, ['record' => $record]);

        activity_log_log_event(
            'after_delete_record',
            $recordName,
            $record->id,
            json_encode($data)
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

    public function filterActivityLogRecordData($data, $args)
    {
        $record = $args['record'];
        $recordName = get_class($record);

        // Provide data about the resource.
        switch ($recordName) {
            case 'Item':
                $data = [
                    'title' => $record->getDisplayTitle(),
                    'item_type_id' => $record->item_type_id,
                    'collection_id' => $record->collection_id,
                    'featured' => $record->featured,
                    'public' => $record->public,
                    'added' => $record->added,
                    'modified' => $record->modified,
                    'owner_id' => $record->owner_id,
                ];
                break;
            case 'Collection':
                $data = [
                    'title' => $record->getDisplayTitle(),
                    'public' => $record->public,
                    'featured' => $record->featured,
                    'added' => $record->added,
                    'modified' => $record->modified,
                    'owner_id' => $record->owner_id,
                ];
                break;
            case 'File':
                $data = [
                    'title' => $record->getDisplayTitle(),
                    'item_id' => $record->item_id,
                    'order' => $record->order,
                    'filename' => $record->filename,
                    'original_filename' => $record->original_filename,
                    'size' => $record->size,
                    'authentication' => $record->authentication,
                    'mime_type' => $record->mime_type,
                    'type_os' => $record->type_os,
                    'has_derivative_image' => $record->has_derivative_image,
                    'added' => $record->added,
                    'modified' => $record->modified,
                    'stored' => $record->stored,
                    'metadata' => $record->metadata,
                    'alt_text' => $record->alt_text,
                ];
                break;
            case 'ItemType':
                $elements = [];
                foreach ($record->Elements as $element) {
                    $elements[] = [
                        'element_set_id' => $element->element_set_id,
                        'order' => $element->order,
                        'name' => $element->name,
                        'description' => $element->description,
                        'comment' => $element->comment,
                    ];
                }
                $data = [
                    'name' => $record->name,
                    'description' => $record->description,
                    'elements' => $elements,
                ];
                break;
        }

        return $data;
    }

    public function filterActivityLogEventMessages($messages, $args)
    {
        $event = $args['event'];
        $record = $event->Record;

        // Add insert, update, and delete event messages.
        if (in_array($event->event, ['after_insert_record', 'after_update_record', 'after_delete_record'])) {
            if ('after_insert_record' === $event->event) {
                $messages[] = sprintf(__('Created a "%s" record'), $event->resource);
            } else if ('after_update_record' === $event->event) {
                $messages[] = sprintf(__('Updated a "%s" record'), $event->resource);
            } else if ('after_delete_record' === $event->event) {
                $messages[] = sprintf(__('Deleted a "%s" record'), $event->resource);
            }
            $messages[] = sprintf(__('ID: %s'), $event->resource_identifier);
            if ($record) {
                $messages[] = link_to($record, null, __('View record'));
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
