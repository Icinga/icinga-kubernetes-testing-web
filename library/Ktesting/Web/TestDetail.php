<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use Icinga\Module\Ktesting\Common\ResourceDetails;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Kubernetes\Web\Details;
use ipl\Html\BaseHtmlElement;
use ipl\I18n\Translation;

class TestDetail extends BaseHtmlElement
{
    use Translation;

    /** @var Test */
    protected $test;

    protected $tag = 'div';

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    protected function assemble()
    {
//        CopyToClipboard::attachTo($icingaStateReason);

        $this->addHtml(
            new Details(new ResourceDetails($this->test, [
                $this->translate('Deployment Name') => $this->test->deployment_name,
            ])),
        );
    }
}
