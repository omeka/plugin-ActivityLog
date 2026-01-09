<?php
class ActivityLog_EventsController extends Omeka_Controller_AbstractActionController
{
    protected $_browseRecordsPerPage = 50;

    public function init()
    {
        $this->_helper->db->setDefaultModelName('ActivityLogEvent');
    }

    protected function _getBrowseDefaultSort()
    {
        // Browse descending by default.
        return ['id', 'd'];
    }
}
