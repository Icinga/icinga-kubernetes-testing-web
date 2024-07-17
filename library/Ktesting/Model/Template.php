<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Model;

use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

class Template extends Model
{
    public function getTableName()
    {
        return 'template';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns()
    {
        return [
            'id',
            'name',
            'created'
        ];
    }

    public function getDefaultSort()
    {
        return ['name'];
    }

    public function createBehaviors(Behaviors $behaviors)
    {
        $behaviors->add(new MillisecondTimestamp([
            'created',
        ]));
    }

    public function createRelations(Relations $relations)
    {
        $relations->hasMany('template_test', TemplateTest::class)
            ->setJoinType('LEFT');
    }
}
