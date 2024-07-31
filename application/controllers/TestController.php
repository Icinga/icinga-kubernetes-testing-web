<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Forms\TestForm;
use Icinga\Module\Ktesting\Forms\DeleteForm;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Ktesting\Web\QuickActions;
use Icinga\Module\Ktesting\Web\TestDetail;
use Icinga\Web\Notification;
use ipl\Html\Attributes;
use ipl\Html\Html;
use ipl\Sql\Select;
use ipl\Stdlib\Filter;
use ipl\Web\Compat\CompatController;
use Ramsey\Uuid\Uuid;
use Exception;

class TestController extends CompatController
{
    public function indexAction(): void
    {
        $this->addTitleTab($this->translate('Test'));

        $uuid = $this->params->getRequired('id');
        $uuidBytes = Uuid::fromString($uuid)->getBytes();

        try {
            /** @var Test $test */
            $test = Test::on(Database::connection())
                ->filter(Filter::equal('uuid', $uuidBytes))
                ->first();

            if ($test === null) {
                $this->httpNotFound($this->translate('Test not found'));
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $this->addControl(new QuickActions($test));
        $this->addContent(new TestDetail($test));
    }

    public function createAction(): void
    {
        $this->addContent(
            Html::tag(
                'h1',
                Attributes::create(),
                $this->translate('Create Test')
            ),
        );

        $createTestForm = (new TestForm())
            ->on(TestForm::ON_SUCCESS, function (TestForm $form) {
                $config = Config::module('ktesting');
                $db = Database::connection();

                $clusterIp = $config->get('api', 'clusterIp');
                $port = $config->get('api', 'apiPort');
                $endpoint = 'test/create';

                $deploymentName = $form->getValue('deploymentName');

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

                $query = "deploymentName="
                    . $deploymentName
                    . "&tests=";

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

                    if ($i > 0) {
                        $query .= ":";
                    }

                    $query .= "$testKind,$totalReplicas,$badReplicas";
                }

                $ch = curl_init("http://$clusterIp:$port/$endpoint?$query");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                try {
                    $response = curl_exec($ch);
                    Notification::info($this->translate($response));
                } catch (Exception $e) {
                    Notification::error($this->translate($e->getMessage()));
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
