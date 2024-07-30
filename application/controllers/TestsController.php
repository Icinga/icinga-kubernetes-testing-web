<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Ktesting\Web\TestList;
use Icinga\Module\Ktesting\Common\Links;
use Icinga\Module\Notifications\Web\Control\SearchBar\ObjectSuggestions;
use ipl\Stdlib\Filter;
use ipl\Web\Compat\CompatController;
use ipl\Web\Compat\SearchControls;
use ipl\Web\Filter\QueryString;
use ipl\Web\Widget\ButtonLink;

class TestsController extends CompatController
{
    use SearchControls;

    public function completeAction(): void
    {
        $suggestions = new ObjectSuggestions();
        $suggestions->setModel(Test::class);
        $suggestions->forRequest($this->getServerRequest());
        $this->getDocument()->add($suggestions);
    }

    /** @var Filter\Rule Filter from query string parameters */
    private $filter;

    public function indexAction(): void
    {
        $tests = Test::on(Database::connection());

        $limitControl = $this->createLimitControl();
        $sortControl = $this->createSortControl(
            $tests,
            [
                'test.name'      => $this->translate('Name'),
                'test.namespace' => $this->translate('Namespace'),
            ]
        );

        $paginationControl = $this->createPaginationControl($tests);
        $searchBar = $this->createSearchBar($tests, [
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

        $tests->filter($filter);

        $this->addControl($paginationControl);
        $this->addControl($sortControl);
        $this->addControl($limitControl);
        $this->addControl($searchBar);
        $this->addContent(
            (new ButtonLink(
                t('New Test'),
                Links::testCreate(),
                'plus',
                [
                    'class' => 'add-test-control'
                ]
            ))->setAttribute('data-base-target', '_next')
//                ->openInModal()
        );

        $this->addContent(new TestList($tests));

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
