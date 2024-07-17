<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class TemplateTest extends Model
{
    public function getTableName()
    {
        return 'template_test';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns()
    {
        return [
            'id',
            'template_id',
            'test_kind',
            'total_replicas',
            'bad_replicas'
        ];
    }

    public function getDefaultSort()
    {
        return ['id'];
    }

    public function createRelations(Relations $relations)
    {
        $relations->hasOne('template', Template::class);
    }
}
