<?php

/* Icinga for
use ipl\Web\Widget\StateBall; Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Ktesting\Web\ListController;
use Icinga\Module\Ktesting\Web\TestList;
use ipl\Orm\Query;

class TestsController extends ListController
{
    protected function getContentClass(): string
    {
        return TestList::class;
    }

    protected function getQuery(): Query
    {
        return Test::on(Database::connection());
    }

    protected function getSortColumns(): array
    {
        return [
            'test.name' => $this->translate('Name'),
            'test.namespace' => $this->translate('Namespace'),
        ];
    }

    protected function getTitle(): string
    {
        return $this->translate('Tests');
    }
}
