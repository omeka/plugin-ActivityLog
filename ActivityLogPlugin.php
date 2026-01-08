<?php
class ActivityLogPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        // 'config_form',
        // 'config',
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

    public function hookConfigForm()
    {
        // @todo
    }

    public function hookConfig($args)
    {
        // @todo
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('ActivityLog_Events');
        $acl->allow(null, 'ActivityLog_Events', array('browse'));
    }

    public function hookAfterSaveRecord($args)
    {
        // @see https://omeka.readthedocs.io/en/latest/Reference/hooks/after_save_record.html
        $record = $args['record'];
        $post = $args['post'];
        $insert = $args['insert'];
        // @todo
    }

    public function hookAfterDeleteRecord($args)
    {
        // @see https://omeka.readthedocs.io/en/latest/Reference/hooks/after_delete_record.html
        $record = $args['record'];
        // @todo
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Activity Log'),
            'uri' => url('activity-log/events'),
            'resource' => ('ActivityLog_Events'),
        );
        return $nav;
    }
}
