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
}
