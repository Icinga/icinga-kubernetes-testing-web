<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

/** @var Module $this */

use Icinga\Application\Modules\Module;

$section = $this->menuSection(
    'Kubernetes Ktesting',
    [
        'icon' => 'globe'
    ]
);

$priority = 0;

$section->add(
    N_('Tests'),
    [
        'description' => $this->translate('List all tests'),
        'url'         => 'ktesting/tests',
        'priority'    => $priority++
    ]
);

$section->add(
    N_('Templates'),
    [
        'description' => $this->translate('List all Templates'),
        'url'         => 'ktesting/templates',
        'priority'    => $priority++
    ]
);

$this->provideConfigTab(
    'api',
    [
        'title' => $this->translate('Ktesting API'),
        'label' => $this->translate('Ktesting API'),
        'url'   => 'config/api',
    ]
);
