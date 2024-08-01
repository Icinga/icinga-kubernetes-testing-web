<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Common\Links;
use Icinga\Module\Ktesting\Forms\TestForm;
use Icinga\Module\Ktesting\Forms\DeleteForm;
use Icinga\Module\Ktesting\Model\Test;
use Icinga\Module\Ktesting\Web\QuickActions;
use Icinga\Module\Ktesting\Web\TestDetail;
use Icinga\Web\Notification;
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
        $this->addTitleTab($this->translate('Create Test'));

        $createTestForm = (new TestForm())
            ->on(TestForm::ON_SUCCESS, function (TestForm $form) {
                $config = Config::module('ktesting');
                $db = Database::connection();

                $clusterIp = $config->get('api', 'clusterIp');
                $port = $config->get('api', 'apiPort');
                $endpoint = 'test/create';

                $resourceName = $form->getValue('resourceName');
                $resourceType = $form->getValue('resourceType');
                $description = $form->getValue('description');
                $expectedPods = $form->getValue('expectedPods');

                if ($resourceName === null) {
                    Notification::error($this->translate('Resource name cannot be empty'));
                    return;
                }

                if ($resourceType === null) {
                    Notification::error($this->translate('Resource type cannot be empty'));
                    return;
                }

                if ($expectedPods <= 0) {
                    Notification::error($this->translate('Expected pods must be greater than 0'));
                    return;
                }

                $rs = Test::on(Database::connection())
                    ->columns(['resource_name'])
                    ->filter(Filter::all(
                        Filter::equal('resource_type', $resourceType),
                        Filter::equal('resource_name', $resourceName)
                    ))
                    ->execute();

                if ($rs->hasResult()) {
                    Notification::error(
                        $this->translate(
                            ucfirst($resourceType) . ' already exists! Please choose another name.'
                        )
                    );
                    return;
                }

                $query =
                    "resourceType=$resourceType"
                    . "&resourceName=$resourceName"
                    . (isset($description) ? "&description=$description" : "")
                    . "&expectedPods=$expectedPods"
                    . "&tests=";

                $testCounter = 0;
                for ($i = 0; ; $i++) {
                    $testKind = $form->getValue("testKind-$i");
                    $testPercentage = $form->getValue("testPercentage-$i");

                    if ($testKind === null || $testPercentage === null) {
                        break;
                    }

                    if ($testPercentage < 1 || $testPercentage > 100) {
                        Notification::error($this->translate('Test percentage must be between 1 and 100'));
                        return;
                    }

                    if ($i > 0) {
                        $query .= ":";
                    }

                    $query .= "$testKind,$testPercentage";
                    $testCounter++;
                }

                if ($testCounter > $expectedPods) {
                    Notification::error($this->translate('Number of tests cannot be greater than expected pods'));
                    return;
                }

                $ch = curl_init("http://$clusterIp:$port/$endpoint?$query");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                try {
                    $response = curl_exec($ch);
                    Notification::info($this->translate($response));
                } catch (Exception $e) {
                    Notification::error($this->translate($e->getMessage()));
                }

                // TODO Go to the test detail page or stay on the same page?

                $this->closeModalAndRefreshRemainingViews(Links::testCreate());
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
