<?php
class Api_ActivityLogEvent extends Omeka_Record_Api_AbstractRecordAdapter implements Zend_Acl_Resource_Interface
{
    public function getResourceId()
    {
        return 'ActivityLog_Events';
    }

    public function getRepresentation(Omeka_Record_AbstractRecord $event)
    {
        return json_decode(json_encode($event), true);
    }
}
