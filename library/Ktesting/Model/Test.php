<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Model;

use Icinga\Module\Kubernetes\Model\Behavior\Uuid;
use ipl\I18n\Translation;
use ipl\Orm\Behavior\MillisecondTimestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;

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
            'uuid'            => $this->translate('UUID'),
            'namespace'       => $this->translate('Namespace'),
            'name'            => $this->translate('Name'),
            'uid'             => $this->translate('UID'),
            'deployment_name' => $this->translate('Deployment Name'),
            'created'         => $this->translate('Created At')
        ];
    }

    public function getColumns(): array
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
}
