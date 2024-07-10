<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use Icinga\Module\Ktesting\Common\BaseListItem;
use Icinga\Module\Ktesting\Common\Links;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Html\HtmlElement;
use ipl\I18n\Translation;
use ipl\Web\Widget\Link;
use ipl\Web\Widget\VerticalKeyValue;
use ipl\Web\Widget\HorizontalKeyValue;

class TestListItem extends BaseListItem
{
    use Translation;

    protected function assembleHeader(BaseHtmlElement $header): void
    {
        $header
            ->addHtml($this->createTitle());
//            ->addHtml(new TimeAgo($this->item->created->getTimestamp()));
    }

    protected function assembleMain(BaseHtmlElement $main): void
    {
        $main->addHtml($this->createHeader());

        $keyValue = new HtmlElement('div', new Attributes(['class' => 'key-value']));
        $keyValue->addHtml(new VerticalKeyValue($this->translate('Name'), $this->item->name));
        $keyValue->addHtml(new VerticalKeyValue($this->translate('UID'), $this->item->uid));
        $keyValue->addHtml(new HtmlElement(
            'div',
            null,
            new HorizontalKeyValue($this->translate('Namespace'), $this->item->namespace),
            new HorizontalKeyValue($this->translate('Deployment Name'), $this->item->deployment_name)
        ));

        $main->addHtml($keyValue);
    }

    protected function assembleTitle(BaseHtmlElement $title): void
    {
        $title->addHtml(Html::sprintf(
            $this->translate('%s', '<test>'),
            new Link($this->item->name, Links::test($this->item), ['class' => 'subject'])
        ));
    }

}
