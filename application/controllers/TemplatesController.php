<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Module\Ktesting\Common\Links;
use Icinga\Module\Ktesting\Web\TemplateList;
use ipl\Web\Compat\CompatController;
use ipl\Web\Compat\SearchControls;
use Icinga\Module\Ktesting\Model\Template;
use Icinga\Module\Ktesting\Common\Database;
use ipl\Web\Filter\QueryString;
use ipl\Web\Widget\ButtonLink;
use ipl\Stdlib\Filter;

class TemplatesController extends CompatController
{
    use SearchControls;

    function indexAction(): void
    {
        $templates = Template::on(Database::connection());

        $limitControl = $this->createLimitControl();
        $sortControl = $this->createSortControl(
            $templates,
            [
                'name'     => $this->translate('Name'),
                'created'  => $this->translate('Created At'),
                'modified' => $this->translate('Modified At'),
            ]
        );

        $paginationControl = $this->createPaginationControl($templates);
        $searchBar = $this->createSearchBar($templates, [
            $limitControl->getLimitParam(),
            $sortControl->getSortParam(),
        ]);

        if ($searchBar->hasBeenSent() && ! $searchBar->isValid()) {
            if ($searchBar->hasBeenSubmitted()) {
                $filter = $this->getFilter();
            } else {
                $this->addControl($searchBar);
                $this->sendMultipartUpdate();
                return;
            }
        } else {
            $filter = $searchBar->getFilter();
        }

        $templates->filter($filter);

        $this->addControl($paginationControl);
        $this->addControl($sortControl);
        $this->addControl($limitControl);
        $this->addControl($searchBar);
        $this->addContent(
            (new ButtonLink(
                t('New Template'),
                Links::templateCreate(),
                'plus',
                [
                    'class' => 'add-template-control'
                ]
            ))->setAttribute('data-base-target', '_next')
//                ->openInModal()
        );

        $this->addContent(new TemplateList($templates));

        if (! $searchBar->hasBeenSubmitted() && $searchBar->hasBeenSent()) {
            $this->sendMultipartUpdate();
        }
    }

    /**
     * Get the filter created from query string parameters
     *
     * @return Filter\Rule
     */
    private function getFilter(): Filter\Rule
    {
        if ($this->filter === null) {
            $this->filter = QueryString::parse((string)$this->params);
        }

        return $this->filter;
    }
}
