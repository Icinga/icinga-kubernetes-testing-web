<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use Icinga\Module\Ktesting\Common\Links;
use Icinga\Module\Ktesting\Model\Test;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Web\Widget\Icon;

class QuickActions extends BaseHtmlElement
{
    /** @var Test */
    protected Test $test;

    protected $tag = 'ul';

    protected $defaultAttributes = ['class' => 'quick-actions'];

    public function __construct($test)
    {
        $this->test = $test;
    }

    protected function assemble()
    {
        $this->assembleAction('delete', 'Delete', 'trash', 'Delete this test');
    }

    protected function assembleAction(string $action, string $label, string $icon, string $title)
    {
        $link = Html::tag(
            'a',
            [
                'href' => Links::$action($this->test)->getAbsoluteUrl(),
                'class' => 'action-link',
                'title' => $title,
                'data-icinga-modal' => true,
                'data-no-icinga-ajax' => true
            ],
            [
                new Icon($icon),
                $label
            ]
        );

        $this->add(Html::tag('li', $link));
    }
}
