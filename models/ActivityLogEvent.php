<?php
class ActivityLogEvent extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
    public $user_id;
    public $timestamp;
    public $ip;
    public $event;
    public $resource;
    public $resource_identifier;
    public $data;

    protected $_related = [
        'User' => 'getUser',
        'Record' => 'getRecord',
        'DateTime' => 'getDateTime',
        'Data' => 'getData',
        'Messages' => 'getMessages',
    ];

    public function getResourceId()
    {
        return 'ActivityLog_Events';
    }

    public function getRecordUrl($action = 'show')
    {
        return url(array(
            'module' => 'activity-log',
            'controller' => 'events',
            'action' => $action,
            'id' => $this->id
        ));
    }

    /**
     * Get the user record of this event.
     *
     * @return User|null
     */
    public function getUser()
    {
        $user = null;
        if ($this->user_id) {
            $user = $this->getTable('User')->find($this->user_id);
        }
        return $user;
    }

    /**
     * Get the resource record of this event.
     *
     * @return Omeka_Record_AbstractRecord|null
     */
    public function getRecord()
    {
        $record = null;
        if ($this->resource) {
            $record = $this->getTable($this->resource)->find($this->resource_identifier);
        }
        return $record;
    }

    /**
     * Get the date/time object of this event.
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%f', $this->timestamp));
        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
        return $dateTime;
    }

    /**
     * Get the data of this event.
     *
     * @return mixed
     */
    public function getData()
    {
        return json_decode($this->data, true);
    }

    /**
     * Get the messages of this event.
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = apply_filters('activity_log_event_messages', [], ['event' => $this]);
        $messages[] = link_to($this, null, __('View event details'));
        return $messages;
    }
}
