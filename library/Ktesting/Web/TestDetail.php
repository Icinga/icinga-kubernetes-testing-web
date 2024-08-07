<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use Icinga\Module\Kubernetes\Common\Database as KDatabase;
use Icinga\Module\Kubernetes\Common\ResourceDetails;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Kubernetes\Model\Deployment;
use Icinga\Module\Kubernetes\Model\Pod;
use Icinga\Module\Kubernetes\Model\ReplicaSet;
use Icinga\Module\Kubernetes\Web\DeploymentList;
use Icinga\Module\Kubernetes\Web\PodList;
use Icinga\Module\Kubernetes\Web\Details;
use ipl\Html\BaseHtmlElement;
use ipl\Html\HtmlElement;
use ipl\I18n\Translation;
use ipl\Html\Text;
use ipl\Stdlib\Filter;

class TestDetail extends BaseHtmlElement
{
    use Translation;

    protected Test $test;

    protected $tag = 'div';

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    protected function assemble(): void
    {
        $resDeployments = Deployment::on(KDatabase::connection())
            ->filter(Filter::equal('owner.owner_uuid', $this->test->uuid))
            ->execute();

        $rulesForReplicaSets = [];
        foreach ($resDeployments as $deployment) {
            $rulesForReplicaSets[] = Filter::equal('owner.owner_uuid', $deployment->uuid);
        }

        $show = false;

        if (!empty($rulesForReplicaSets)) {
            $show = true;
            $resReplicaSets = ReplicaSet::on(KDatabase::connection())
                ->filter(Filter::any(...$rulesForReplicaSets))
                ->execute();

            $rulesForPods = [];
            foreach ($resReplicaSets as $replicaSet) {
                $rulesForPods[] = Filter::equal('owner.owner_uuid', $replicaSet->uuid);
            }

            if (!empty($rulesForPods)) {
                $resPods = Pod::on(KDatabase::connection())
                    ->filter(Filter::any(...$rulesForPods))
                    ->execute();
            }
        }

        $this->addHtml(
            new Details(new ResourceDetails($this->test, [
                $this->translate('Deployment Name') => $this->test->deployment_name,
            ])),
            new HtmlElement(
                'section',
                null,
                new HtmlElement('h2', null, new Text($this->translate('Deployments'))),
                ($show) ? new DeploymentList($resDeployments) : new HtmlElement('span')
            ),
            new HtmlElement(
                'section',
                null,
                new HtmlElement('h2', null, new Text($this->translate('Pods'))),
                (isset($resPods) && $resPods->hasResult()) ? new PodList($resPods) : new HtmlElement('span')
            )
        );
    }
}
