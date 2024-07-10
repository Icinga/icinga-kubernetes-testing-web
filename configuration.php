<?php

/* Icinga for Kubernetes Web | (c) 2024 Icinga GmbH | GPLv2 */

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
    N_('Create'),
    [
        'description' => $this->translate('Create new test'),
        'url'         => 'ktesting/testing/create',
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
