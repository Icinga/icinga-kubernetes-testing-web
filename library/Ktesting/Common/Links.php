<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Common;

use Icinga\Module\Ktesting\Model\Test;
use ipl\Web\Url;
use Ramsey\Uuid\Uuid;

abstract class Links
{
    public static function test(Test $test): Url
    {
        return Url::fromPath('ktesting/tests', ['id' => (string) Uuid::fromBytes($test->uuid)]);
    }
}
