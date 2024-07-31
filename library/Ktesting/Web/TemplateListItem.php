<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use DateTime;
use Icinga\Module\Kubernetes\Common\BaseListItem;
use Icinga\Module\Ktesting\Common\Links;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Html\HtmlElement;
use ipl\I18n\Translation;
use ipl\Web\Widget\Link;
use ipl\Web\Widget\TimeAgo;
use ipl\Web\Widget\VerticalKeyValue;

class TemplateListItem extends BaseListItem
{
    use Translation;

    protected function assembleHeader(BaseHtmlElement $header): void
    {
        $header
            ->addHtml($this->createTitle())
            ->addHtml(new TimeAgo($this->item->created->getTimestamp()));
    }

    protected function assembleMain(BaseHtmlElement $main): void
    {
        $main->addHtml($this->createHeader());

        $keyValue = new HtmlElement('div', new Attributes(['class' => 'key-value']));

        $keyValue->addHtml(new VerticalKeyValue(
            $this->translate('Created'),
            (new DateTime())->setTimestamp($this->item->created->getTimestamp())->format('Y-m-d H:i:s')
        ));
        $keyValue->addHtml(new VerticalKeyValue(
            $this->translate('Modified'),
            ($this->item->modified) ? (new DateTime())->setTimestamp($this->item->modified->getTimestamp())->format('Y-m-d H:i:s') : '-'
        ));

        $main->addHtml($keyValue);
    }

    protected function assembleTitle(BaseHtmlElement $title): void
    {
        $title->addHtml(Html::sprintf(
            $this->translate('%s', '<test>'),
            new Link($this->item->name, Links::templateUpdate($this->item), ['class' => 'subject']),
        ));
    }

}
