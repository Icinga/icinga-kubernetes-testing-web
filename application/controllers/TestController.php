<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Ktesting\Web\QuickActions;
use Icinga\Module\Ktesting\Web\TestDetail;
use ipl\Stdlib\Filter;
use ipl\Web\Compat\CompatController;
use Ramsey\Uuid\Uuid;
use Exception;

class TestController extends CompatController
{
    public function indexAction(): void
    {
        $this->addTitleTab($this->translate('Test'));

        $uuid = $this->params->getRequired('id');
        $uuidBytes = Uuid::fromString($uuid)->getBytes();

        try {
            /** @var Test $test */
            $test = Test::on(Database::connection())
                ->filter(Filter::equal('uuid', $uuidBytes))
                ->first();

            if ($test === null) {
                $this->httpNotFound($this->translate('Test not found'));
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $this->addControl(new QuickActions($test));
        $this->addContent(new TestDetail($test));
    }
}
