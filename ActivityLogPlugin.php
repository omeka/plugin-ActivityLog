<?php
class ActivityLogPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'define_acl',
        // Log changes to records.
        'after_save_record',
        'after_delete_record',
        // Log changes to options.
        'insert_option',
        'update_option',
        'delete_option',
        // Log changes to plugins.
        'install_plugin',
        'uninstall_plugin',
        'activate_plugin',
        'deactivate_plugin',
        'upgrade_plugin',
    );

    protected $_filters = array(
        'admin_navigation_main',
        'activity_log_event_messages',
    );

    public function hookInstall()
    {
        $db = get_db();
        $sql = "
        CREATE TABLE IF NOT EXISTS `$db->ActivityLogEvent` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` int UNSIGNED DEFAULT NULL,
            `timestamp` double NOT NULL,
            `ip` varchar(45) DEFAULT NULL,
            `event` varchar(255) NOT NULL,
            `resource` varchar(255) DEFAULT NULL,
            `resource_identifier` varchar(255) DEFAULT NULL,
            `data` longtext,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $db->query($sql);
    }

    public function hookUninstall()
    {
        $db = get_db();
        $db->query("DROP TABLE IF EXISTS `$db->ActivityLogEvent`");
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
            json_encode($args)
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
            json_encode($args)
        );
    }

    public function hookInsertOption($args)
    {
        activity_log_log_event(
            'insert_option',
            'option',
            $args['name'],
            json_encode($args)
        );
    }

    public function hookUpdateOption($args)
    {
        activity_log_log_event(
            'update_option',
            'option',
            $args['name'],
            json_encode($args)
        );
    }

    public function hookDeleteOption($args)
    {
        activity_log_log_event(
            'delete_option',
            'option',
            $args['name'],
            json_encode($args)
        );
    }

    public function hookInstallPlugin($args)
    {
        activity_log_log_event(
            'install_plugin',
            'plugin',
            $args['plugin']->name,
            json_encode($args)
        );
    }

    public function hookUninstallPlugin($args)
    {
        activity_log_log_event(
            'uninstall_plugin',
            'plugin',
            $args['plugin']->name,
            json_encode($args)
        );
    }

    public function hookActivatePlugin($args)
    {
        activity_log_log_event(
            'activate_plugin',
            'plugin',
            $args['plugin']->name,
            json_encode($args)
        );
    }

    public function hookDeactivatePlugin($args)
    {
        activity_log_log_event(
            'deactivate_plugin',
            'plugin',
            $args['plugin']->name,
            json_encode($args)
        );
    }

    public function hookUpgradePlugin($args)
    {
        activity_log_log_event(
            'upgrade_plugin',
            'plugin',
            $args['plugin']->name,
            json_encode($args)
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

        // Add event messages.
        switch ($event->event) {
            case 'after_insert_record':
                $messages[] = sprintf(__('Created a "%s" record'), $event->resource);
                break;
            case 'after_update_record':
                $messages[] = sprintf(__('Updated a "%s" record'), $event->resource);
                break;
            case 'after_delete_record':
                $messages[] = sprintf(__('Deleted a "%s" record'), $event->resource);
                break;
            case 'insert_option':
                $messages[] = __('Inserted an option');
                break;
            case 'update_option':
                $messages[] = __('Updated an option');
                break;
            case 'delete_option':
                $messages[] = __('Deleted an option');
                break;
            case 'install_plugin':
                $messages[] = __('Installed a plugin');
                break;
            case 'uninstall_plugin':
                $messages[] = __('Uninstalled a plugin');
                break;
            case 'activate_plugin':
                $messages[] = __('Activated a plugin');
                break;
            case 'deactivate_plugin':
                $messages[] = __('Deactivated a plugin');
                break;
            case 'upgrade_plugin':
                $messages[] = __('Upgraded a plugin');
                break;
        }

        // Add ID message.
        $messages[] = sprintf(__('ID: %s'), $event->resource_identifier);

        // Add record link message.
        $hooks = [
            'after_insert_record',
            'after_update_record',
            'after_delete_record',
        ];
        if (in_array($event->event, $hooks)) {

            $record = $event->Record;
            if ($record) {
                // Only provide links to records that can be resolved.
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
        }

        return $messages;
    }
}

/**
 * Log an event.
 *
 * Plugins can use this function in their hook handlers to add events.
 *
 * @param string $event The event name (typically the related hook name)
 * @param string $resource The resource name
 * @param string $resource_identifier The resource identifier (if any)
 * @param string $data The data used to perform the event (if any)
 */
function activity_log_log_event($event, $resource, $resource_identifier, $data)
{
    $db = get_db();
    try {
        $eventId = $db->insert('ActivityLogEvents', [
            'user_id' => current_user()->id,
            'timestamp' => microtime(true),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'event' => $event,
            'resource' => $resource,
            'resource_identifier' => $resource_identifier,
            'data' => $data,
        ]);
    } catch (Exception $e) {
        // Catch throwable errors and log them instead of breaking the page.
        _log(sprintf(__('ActivityLog exception: %s'), $e->getMessage()));
        return;
    }
    return $eventId;
}
