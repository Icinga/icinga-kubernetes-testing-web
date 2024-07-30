<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Common;

use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Ktesting\Model\Template;
use ipl\Web\Url;
use Ramsey\Uuid\Uuid;

abstract class Links
{
    public static function test(Test $test): Url
    {
        return Url::fromPath('ktesting/test', ['id' => (string)Uuid::fromBytes($test->uuid)]);
    }

    public static function testDelete(Test $test): Url
    {
        return Url::fromPath('ktesting/test/delete', ['namespace' => $test->namespace, 'name' => $test->name]);
    }

    public static function testCreate(): Url
    {
        return Url::fromPath('ktesting/test/create');
    }

    public static function templates(): Url
    {
        return Url::fromPath('ktesting/templates');
    }

    public static function templateCreate(): Url
    {
        return Url::fromPath('ktesting/template/create');
    }

    public static function templateUpdate(Template $template): Url
    {
        return Url::fromPath('ktesting/template/update', ['id' => $template->id]);
    }
}
