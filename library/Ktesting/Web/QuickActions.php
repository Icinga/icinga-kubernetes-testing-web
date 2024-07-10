<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use Icinga\Module\Ktesting\Common\Links;
use Icinga\Module\Ktesting\Forms\DeleteForm;
use Icinga\Module\Ktesting\Model\Test;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

class QuickActions extends BaseHtmlElement
{
    protected Test $test;

    protected $tag = 'ul';

    protected $defaultAttributes = ['class' => 'quick-actions'];

    public function __construct($test)
    {
        $this->test = $test;
    }

    protected function assemble()
    {
        $this->add(Html::tag('li', (new DeleteForm())->setAction(Links::delete($this->test)->getAbsoluteUrl())));
    }
}
