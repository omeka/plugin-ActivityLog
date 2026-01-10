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
    ];

    public function getResourceId()
    {
        return 'ActivityLog_Event';
    }

    public function getUser()
    {
        $user = null;
        if ($this->user_id) {
            $user = $this->getTable('User')->find($this->user_id);
        }
        return $user;
    }

    public function getRecord()
    {
        $record = null;
        if ($this->resource) {
            $record = $this->getTable($this->resource)->find($this->resource_identifier);
        }
        return $record;
    }

    public function getDateTime()
    {
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%f', $this->timestamp));
        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
        return $dateTime;
    }

    public function getData()
    {
        return json_decode($this->data, true);
    }
}
