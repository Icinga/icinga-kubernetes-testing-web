<?php

/* Icinga for Kubernetes Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Ktesting\Forms\CreateTestForm;
use Icinga\Module\Ktesting\Web\Controller;
use Icinga\Module\Ktesting\Web\NavigationList;
use Icinga\Web\Notification;
use ipl\Html\Text;
use ipl\Html\Html;
use ipl\Html\Attributes;

class TestController extends Controller
{
    public function indexAction(): void
    {
        $this->addContent(
            Html::tag('h1', Attributes::create(), $this->translate('Kubernetes Testing')),
        );

        $this->addContent(
            Html::tag('a', Attributes::create(['href' => 'ktesting/test/create']), $this->translate('Create Test')),
        );
    }

    public function createAction(): void
    {
        $this->addTitleTab($this->translate('Create'));

        $this->addContent(
            Html::tag('h1', Attributes::create(), $this->translate('Create Test')),
        );

        $createTestForm = (new CreateTestForm())
            ->on(CreateTestForm::ON_SUCCESS, function (CreateTestForm $form) {
            $config = Config::module('ktesting');
            $clusterIp = $config->get('api', 'clusterip');
            $port = $config->get('api', 'port');
            $endpoint = 'test/create';
            $query = "deploymentName="
                . $form->getValue('deploymentName')
                . "&tests="
                . $form->getValue('testKind')
                . ","
                . $form->getValue('goodReplicas')
                . ","
                . $form->getValue('badReplicas');

            for ($i = 0; ; $i++) {
                $testKind = $form->getValue("testKind-$i");
                $goodReplicas = $form->getValue("goodReplicas-$i");
                $badReplicas = $form->getValue("badReplicas-$i");

                if ($testKind === null || $goodReplicas === null || $badReplicas === null) {
                    break;
                }

                $query .= ":    $testKind,$goodReplicas,$badReplicas";
            }

            $ch = curl_init("http://$clusterIp:$port/$endpoint?$query");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            try {
                $response = curl_exec($ch);
                Notification::info($this->translate($response));
            } catch (\Exception $e) {
                $response = $e->getMessage();
                Notification::error($this->translate($response));
            }
        })->handleRequest($this->getServerRequest());

        $this->addContent($createTestForm);

    }
}
