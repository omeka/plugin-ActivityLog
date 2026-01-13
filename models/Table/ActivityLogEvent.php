<?php
class Table_ActivityLogEvent extends Omeka_Db_Table
{
    public function getResourceValueOptions()
    {
        $tableName = $this->getTableName();
        $sql = sprintf('SELECT resource, COUNT(resource) count
            FROM %s
            GROUP BY resource
            ORDER BY resource',
            $tableName
        );
        $stmt = $this->getDb()->query($sql);
        $valueOptions = ['' => __('Select a resource')];
        foreach ($stmt->fetchAll() as $resource) {
            $valueOptions[$resource['resource']] = sprintf('%s (%s)', $resource['resource'], $resource['count']);
        }
        return $valueOptions;
    }

    public function getEventValueOptions()
    {
        $tableName = $this->getTableName();
        $sql = sprintf('SELECT event, COUNT(event) count
            FROM %s
            GROUP BY event
            ORDER BY event',
            $tableName
        );
        $stmt = $this->getDb()->query($sql);
        $valueOptions = ['' => __('Select an event by name')];
        foreach ($stmt->fetchAll() as $resource) {
            $valueOptions[$resource['event']] = sprintf('%s (%s)', $resource['event'], $resource['count']);
        }
        return $valueOptions;
    }
}
