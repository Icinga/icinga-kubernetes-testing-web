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
    N_('Test'),
    [
        'description' => $this->translate('Test'),
        'url'         => 'ktesting/test',
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
