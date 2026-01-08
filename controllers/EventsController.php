<?php
class ActivityLog_EventsController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ActivityLogEvent');
    }
}
