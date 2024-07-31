<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use Icinga\Module\Kubernetes\Common\BaseItemList;

class TestList extends BaseItemList
{
    protected function getItemClass(): string
    {
        return TestListItem::class;
    }
}
