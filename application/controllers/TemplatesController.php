<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Module\Ktesting\Web\Controller;
use ipl\Html\Html;
use ipl\Html\Attributes;
use Icinga\Module\Ktesting\Web\CreateAndTemplatesTabs;
use Icinga\Module\Ktesting\Model\Template;
use Icinga\Module\Ktesting\Common\Database;
use ipl\Web\Url;
use ipl\Web\Widget\Link;

class TemplatesController extends Controller
{
    use CreateAndTemplatesTabs;

    function indexAction(): void
    {
        $this->createTabs()->activate('templates');

        $this->addContent(
            Html::tag(
                'h1',
                Attributes::create(),
                $this->translate('Templates')
            ),
        );

        $templates = Template::on(Database::connection());

        $sortControl = $this->createSortControl(
            $templates,
            [
                'name'      => $this->translate('Name'),
                'created'   => $this->translate('Created At'),
            ]
        );

        $this->addControl($sortControl);

        $tableRows = [];

        foreach ($templates as $template) {
            $tableRows[] = Html::tag('tr', null, [
                Html::tag(
                    'td',
                    null,
                    new Link($template->name, Url::fromPath('ktesting/template/edit', ['id' => $template->id]))
                ),
                Html::tag('td', null, $template->created->format('Y-m-d H:i')),
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                ['class' => 'common-table table-row-selectable', 'data-base-target' => '_next'],
                [
                    Html::tag(
                        'thead',
                        null,
                        Html::tag(
                            'tr',
                            null,
                            [
                                Html::tag('th', null, 'Name'),
                                Html::tag('th', null, 'Date Created'),
                            ]
                        )
                    ),
                    Html::tag('tbody', null, $tableRows)
                ]
            );

            $this->addContent($table);
        } else {
            $this->addContent(Html::tag('p', null, 'No templates created yet.'));
        }
    }
}
