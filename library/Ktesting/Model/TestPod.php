<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class TestPod extends Model
{
    public function getTableName()
    {
        return 'test_pod';
    }

    public function getKeyName() {}

    public function getColumns()
    {
        return [
            'test_uuid',
            'kind',
            'percentage',
        ];
    }

    public function getDefaultSort()
    {
        return ['test_uuid'];
    }

    public function createRelations(Relations $relations)
    {
        $relations->belongsTo('test', Test::class);
    }
}
