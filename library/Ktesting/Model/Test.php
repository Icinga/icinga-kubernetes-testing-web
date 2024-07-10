<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Model;

use Icinga\Module\Ktesting\Model\Behavior\Uuid;
use ipl\I18n\Translation;
//use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

class Test extends Model
{
    use Translation;

    public function createBehaviors(Behaviors $behaviors)
    {
        $behaviors->add(new Uuid([
            'uuid'
        ]));

        $behaviors->add(new MillisecondTimestamp([
            'created'
        ]));
    }

    public function createRelations(Relations $relations)
    {
    }

    public function getColumnDefinitions()
    {
        return [
            'uuid'            => $this->translate('UUID'),
            'namespace'       => $this->translate('Namespace'),
            'name'            => $this->translate('Name'),
            'uid'             => $this->translate('UID'),
            'deployment_name' => $this->translate('Deployment Name'),
            'created'         => $this->translate('Created At')
        ];
    }

    public function getColumns()
    {
        return [
            'uuid',
            'namespace',
            'name',
            'uid',
            'deployment_name',
            'created',
        ];
    }

    public function getDefaultSort()
    {
        return ['name asc'];
    }

    public function getKeyName()
    {
        return 'uuid';
    }

    public function getSearchColumns()
    {
        return ['name'];
    }

    public function getTableName()
    {
        return 'test';
    }
}
