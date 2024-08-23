<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Model;

use Icinga\Module\Kubernetes\Model\Behavior\Uuid;
use ipl\I18n\Translation;
use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

class Test extends Model
{
    use Translation;

    public function createBehaviors(Behaviors $behaviors): void
    {
        $behaviors->add(new Uuid([
            'uuid'
        ]));

        $behaviors->add(new MillisecondTimestamp([
            'created'
        ]));
    }

    public function getColumnDefinitions(): array
    {
        return [
            'uuid'             => $this->translate('UUID'),
            'namespace'        => $this->translate('Namespace'),
            'name'             => $this->translate('Name'),
            'uid'              => $this->translate('UID'),
            'resource_version' => $this->translate('Resource Version'),
            'resource_type'    => $this->translate('Resource Type'),
            'resource_name'    => $this->translate('Resource Name'),
            'description'      => $this->translate('Description'),
            'expected_pods'    => $this->translate('Expected Pods'),
            'created'          => $this->translate('Created At')
        ];
    }

    public function getColumns(): array
    {
        return [
            'uuid',
            'namespace',
            'name',
            'uid',
            'resource_version',
            'resource_type',
            'resource_name',
            'description',
            'expected_pods',
            'created',
        ];
    }

    public function getDefaultSort(): array
    {
        return ['name asc'];
    }

    public function getKeyName(): string
    {
        return 'uuid';
    }

    public function getSearchColumns(): array
    {
        return ['name'];
    }

    public function getTableName(): string
    {
        return 'test';
    }

    public function createRelations(Relations $relations)
    {
        $relations->hasMany('test_pod', TestPod::class)->setJoinType('LEFT');
    }
}
