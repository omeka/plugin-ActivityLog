<?php
class Table_ActivityLogEvent extends Omeka_Db_Table
{
    public function applySearchFilters($select, $params)
    {
        // Apply default filters (via column names).
        parent::applySearchFilters($select, $params);

        $db = $this->getDb();

        // Apply custom filters.
        if (isset($params['user_role'])) {
            $select->joinInner(['users' => $db->User], 'users.id = activity_log_events.user_id', []);
            $select->where('users.role = ?', $params['user_role']);
        }
        if (isset($params['from'])) {
            $date = new Zend_Date($params['from'], Zend_Date::ISO_8601);
            $date->setTimezone(date_default_timezone_get());
            $select->where('activity_log_events.timestamp >= ?', $date->getTimestamp());
        }
        if (isset($params['before'])) {
            $date = new Zend_Date($params['before'], Zend_Date::ISO_8601);
            $date->setTimezone(date_default_timezone_get());
            $select->where('activity_log_events.timestamp < ?', $date->getTimestamp());
        }
    }

    /**
     * Get value options for a resource select element.
     *
     * @return array
     */
    public function getResourceValueOptions()
    {
        $db = $this->getDb();
        $sql = sprintf('SELECT resource, COUNT(resource) count
            FROM %s
            GROUP BY resource
            ORDER BY resource',
            $db->ActivityLogEvent
        );
        $stmt = $this->getDb()->query($sql);
        $valueOptions = ['' => __('Select a resource')];
        foreach ($stmt->fetchAll() as $result) {
            $valueOptions[$result['resource']] = sprintf('%s (%s)', $result['resource'], $result['count']);
        }
        return $valueOptions;
    }

    /**
     * Get value options for a event select element.
     *
     * @return array
     */
    public function getEventValueOptions()
    {
        $db = $this->getDb();
        $sql = sprintf('SELECT event, COUNT(event) count
            FROM %s
            GROUP BY event
            ORDER BY event',
            $db->ActivityLogEvent
        );
        $stmt = $this->getDb()->query($sql);
        $valueOptions = ['' => __('Select an event by name')];
        foreach ($stmt->fetchAll() as $result) {
            $valueOptions[$result['event']] = sprintf('%s (%s)', $result['event'], $result['count']);
        }
        return $valueOptions;
    }

    /**
     * Get value options for a user select element.
     *
     * @return array
     */
    public function getUserValueOptions()
    {
        $db = $this->getDb();
        $sql = sprintf('SELECT users.id, users.username, COUNT(activity_log_events.user_id) count
            FROM %s activity_log_events
            INNER JOIN %s users ON activity_log_events.user_id = users.id
            GROUP BY activity_log_events.user_id
            ORDER BY users.username',
            $db->ActivityLogEvent,
            $db->User
        );
        $stmt = $this->getDb()->query($sql);
        $valueOptions = ['' => __('Select a user')];
        foreach ($stmt->fetchAll() as $result) {
            $valueOptions[$result['id']] = sprintf('%s (%s)', $result['username'], $result['count']);
        }
        return $valueOptions;
    }

    /**
     * Get value options for a user role select element.
     *
     * @return array
     */
    public function getUserRoleValueOptions()
    {
        $db = $this->getDb();
        $sql = sprintf('SELECT users.role, COUNT(users.role) count
            FROM %s activity_log_events
            INNER JOIN %s users ON activity_log_events.user_id = users.id
            GROUP BY users.role
            ORDER by users.role',
            $db->ActivityLogEvent,
            $db->User
        );
        $stmt = $this->getDb()->query($sql);
        $valueOptions = ['' => __('Select a user role')];
        foreach ($stmt->fetchAll() as $result) {
            $valueOptions[$result['role']] = sprintf('%s (%s)', $result['role'], $result['count']);
        }
        return $valueOptions;
    }
}
