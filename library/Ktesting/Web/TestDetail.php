<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

use Icinga\Module\Kubernetes\Common\Database as KDatabase;
use Icinga\Module\Kubernetes\Common\ResourceDetails;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Kubernetes\Model\DaemonSet;
use Icinga\Module\Kubernetes\Model\Deployment;
use Icinga\Module\Kubernetes\Model\Pod;
use Icinga\Module\Kubernetes\Model\ReplicaSet;
use Icinga\Module\Kubernetes\Model\StatefulSet;
use Icinga\Module\Kubernetes\Web\DaemonSetList;
use Icinga\Module\Kubernetes\Web\DeploymentList;
use Icinga\Module\Kubernetes\Web\PodList;
use Icinga\Module\Kubernetes\Web\Details;
use Icinga\Module\Kubernetes\Web\ReplicaSetList;
use Icinga\Module\Kubernetes\Web\StatefulSetList;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Html\HtmlElement;
use ipl\I18n\Translation;
use ipl\Html\Text;
use ipl\Orm\ResultSet;
use ipl\Stdlib\Filter;
use mysql_xdevapi\Result;

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
        switch ($this->test->resource_type) {
            case 'deployment':
                $resDeployments = Deployment::on(KDatabase::connection())
                    ->filter(Filter::equal('owner.owner_uuid', $this->test->uuid))
                    ->execute();

                $rulesForReplicaSets = [];
                foreach ($resDeployments as $deployment) {
                    $rulesForReplicaSets[] = Filter::equal('owner.owner_uuid', $deployment->uuid);
                }

                $show = false;

                if (! empty($rulesForReplicaSets)) {
                    $show = true;
                    $resReplicaSets = ReplicaSet::on(KDatabase::connection())
                        ->filter(Filter::any(...$rulesForReplicaSets))
                        ->execute();

                    $rulesForPods = [];
                    foreach ($resReplicaSets as $replicaSet) {
                        $rulesForPods[] = Filter::equal('owner.owner_uuid', $replicaSet->uuid);
                    }

                    if (! empty($rulesForPods)) {
                        $resPods = Pod::on(KDatabase::connection())
                            ->filter(Filter::any(...$rulesForPods))
                            ->execute();
                    }
                }

                $resourceList = new HtmlElement(
                    'section',
                    null,
                    new HtmlElement('h2', null, new Text($this->translate('Deployment'))),
                    ($show) ? new DeploymentList($resDeployments) : new HtmlElement('span')
                );
                break;
            case 'replicaset':
                $resReplicaSets = ReplicaSet::on(KDatabase::connection())
                    ->filter(Filter::equal('owner.owner_uuid', $this->test->uuid))
                    ->execute();

                $rulesForPods = [];
                foreach ($resReplicaSets as $replicaSet) {
                    $rulesForPods[] = Filter::equal('owner.owner_uuid', $replicaSet->uuid);
                }

                if (! empty($rulesForPods)) {
                    $resPods = Pod::on(KDatabase::connection())
                        ->filter(Filter::any(...$rulesForPods))
                        ->execute();
                }
                $resourceList = new HtmlElement(
                    'section',
                    null,
                    new HtmlElement('h2', null, new Text($this->translate('ReplicaSet'))),
                    new ReplicaSetList($resReplicaSets)
                );
                break;
            case 'statefulset':
                $resStatefulSets = StatefulSet::on(KDatabase::connection())
                    ->filter(Filter::equal('owner.owner_uuid', $this->test->uuid))
                    ->execute();

                $rulesForPods = [];
                foreach ($resStatefulSets as $statefulSet) {
                    $rulesForPods[] = Filter::equal('owner.owner_uuid', $statefulSet->uuid);
                }

                if (! empty($rulesForPods)) {
                    $resPods = Pod::on(KDatabase::connection())
                        ->filter(Filter::any(...$rulesForPods))
                        ->execute();
                }
                $resourceList = new HtmlElement(
                    'section',
                    null,
                    new HtmlElement('h2', null, new Text($this->translate('StatefulSet'))),
                    new StatefulSetList($resStatefulSets)
                );
                break;
            case 'daemonset':
                $resDaemonSets = DaemonSet::on(KDatabase::connection())
                    ->filter(Filter::equal('owner.owner_uuid', $this->test->uuid))
                    ->execute();

                $rulesForPods = [];
                foreach ($resDaemonSets as $daemonSet) {
                    $rulesForPods[] = Filter::equal('owner.owner_uuid', $daemonSet->uuid);
                }

                if (! empty($rulesForPods)) {
                    $resPods = Pod::on(KDatabase::connection())
                        ->filter(Filter::any(...$rulesForPods))
                        ->execute();
                }
                $resourceList = new HtmlElement(
                    'section',
                    null,
                    new HtmlElement('h2', null, new Text($this->translate('DaemonSet'))),
                        new DaemonSetList($resDaemonSets)
                    );
                break;
        }


        $this->addHtml(
            new Details(new ResourceDetails($this->test, [
                $this->translate('Resource Type') => ucfirst($this->test->resource_type),
                $this->translate('Resource Name') => $this->test->resource_name,
                $this->translate('Description')   =>
                    ($this->test->description !== '') ? $this->test->description : Html::tag(
                        'p',
                        Attributes::create(['class' => 'empty-state']),
                        'No description available'
                    ),
            ])),
            $resourceList,
            new HtmlElement(
                'section',
                null,
                new HtmlElement('h2', null, new Text($this->translate("Pods ({$this->test->expected_pods} expected)"))),
                (isset($resPods) && $resPods->hasResult()) ? new PodList($resPods) : new HtmlElement('span')
            )
        );
    }
}
