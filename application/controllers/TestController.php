<?php

/* Icinga for Kubernetes Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Ktesting\Web\Controller;
use Icinga\Module\Ktesting\Web\NavigationList;
use Icinga\Web\Notification;
use ipl\Html\Text;

class TestController extends Controller
{
    public function indexAction(): void
    {
        $this->addContent(new NavigationList([
            ['href' => 'ktesting/test/api?endpoint=cpu', 'text' => 'Increase CPU Usage'],
            ['href' => 'ktesting/test/api?endpoint=memory', 'text' => 'Increase Memory Usage'],
            ['href' => 'ktesting/test/api?endpoint=disk', 'text' => 'Increase Disk Usage'],
            ['href' => 'ktesting/test/api?endpoint=network', 'text' => 'Start Network Problems'],
            ['href' => 'ktesting/test/api?endpoint=oom', 'text' => 'Trigger OOM Killer'],
            ['href' => 'ktesting/test/api?endpoint=crash', 'text' => 'Crash Application'],
            ['href' => 'ktesting/test/api?endpoint=stopReadiness', 'text' => 'Stop Readiness'],
            ['href' => 'ktesting/test/api?endpoint=stopLiveness', 'text' => 'Stop Liveness'],
            ['href' => 'ktesting/test/api?endpoint=readiness', 'text' => 'Check Readiness'],
            ['href' => 'ktesting/test/api?endpoint=liveness', 'text' => 'Check Liveness'],
        ]));
    }

    public function apiAction(): void
    {
        $config = Config::module('ktesting');
        $clusterIp = $config->get('api', 'clusterip');
        $port = $config->get('api', 'port');
        $endpoint = $this->params->get('endpoint');

        $ch = curl_init("http://$clusterIp:$port/$endpoint");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        try {
            $response = curl_exec($ch);
            Notification::info($this->translate($response));
        } catch (\Exception $e) {
            $response = $e->getMessage();
            Notification::error($this->translate($response));
        }

        $this->redirectNow('ktesting/test');
    }
}
