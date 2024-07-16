<?php

/* Icinga for Kubernetes Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Forms\CreateTestForm;
use Icinga\Module\Ktesting\Forms\DeleteForm;
use Icinga\Module\Ktesting\Web\Controller;
use Icinga\Module\Ktesting\Web\NavigationList;
use Icinga\Web\Notification;
use ipl\Html\Text;
use ipl\Html\Html;
use ipl\Html\Attributes;
use ipl\Sql\Select;

class TestingController extends Controller
{
    public function createAction(): void
    {
        $this->addTitleTab($this->translate('Create'));

        $this->addContent(
            Html::tag(
                'h1',
                Attributes::create(),
                $this->translate('Create Test')
            ),
        );

        $createTestForm = (new CreateTestForm())
            ->on(CreateTestForm::ON_SUCCESS, function (CreateTestForm $form) {
                $config = Config::module('ktesting');
                $db = Database::connection();

                $clusterIp = $config->get('api', 'clusterIp');
                $port = $config->get('api', 'apiPort');
                $endpoint = 'test/create';

                $deploymentName = $form->getValue('deploymentName');
                $testKind = $form->getValue('testKind');
                $totalReplicas = $form->getValue("totalReplicas");
                $badReplicas = $form->getValue("badReplicas");

                $rs = $db->yieldAll(
                    (new Select())
                        ->columns('deployment_name')
                        ->from('test')
                );

                foreach ($rs as $row) {
                    if ($row->deployment_name === $deploymentName) {
                        Notification::error($this->translate('Deployment name already exists'));
                        return;
                    }
                }

                if ($totalReplicas < $badReplicas) {
                    Notification::error($this->translate('Bad replicas cannot be greater than total replicas'));
                    return;
                }

                if ($totalReplicas == 0) {
                    Notification::error($this->translate('Total replicas cannot be 0'));
                    return;
                }

                $query = "deploymentName="
                    . $deploymentName
                    . "&tests="
                    . $testKind
                    . ","
                    . $totalReplicas
                    . ","
                    . $badReplicas;

                for ($i = 0; ; $i++) {
                    $testKind = $form->getValue("testKind-$i");
                    $totalReplicas = $form->getValue("totalReplicas-$i");
                    $badReplicas = $form->getValue("badReplicas-$i");

                    if ($testKind === null || $totalReplicas === null || $badReplicas === null) {
                        break;
                    }

                    if ($totalReplicas < $badReplicas) {
                        Notification::error($this->translate('Bad replicas cannot be greater than total replicas'));
                        return;
                    }

                    if ($totalReplicas == 0) {
                        Notification::error($this->translate('Total replicas cannot be 0'));
                        return;
                    }

                    $query .= ":$testKind,$totalReplicas,$badReplicas";
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

    public function deleteAction(): void
    {
        (new DeleteForm())
            ->on(DeleteForm::ON_SUCCESS, function (DeleteForm $form) {
                $config = Config::module('ktesting');

                $clusterIp = $config->get('api', 'clusterIp');
                $port = $config->get('api', 'apiPort');
                $endpoint = 'test/delete';

                $namespace = $this->params->get('namespace');
                $name = $this->params->get('name');

                $query = "tests=$namespace/$name";

                $ch = curl_init("http://$clusterIp:$port/$endpoint?$query");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                try {
                    $response = curl_exec($ch);
                    Notification::info($this->translate($response));
                } catch (\Exception $e) {
                    $response = $e->getMessage();
                    Notification::error($this->translate($response));
                }

                $this->redirectNow('ktesting/tests');
            })->handleRequest($this->getServerRequest());

    }
}
