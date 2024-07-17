<?php

/* Icinga for Kubernetes Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Web;

trait CreateAndTemplatesTabs
{
    /**
     * Create tabs
     *
     * @return  \ipl\Web\Widget\Tabs
     */
    protected function createTabs()
    {
        $tabs = $this->getTabs();
        $tabs->getAttributes()->set('data-base-target', '_main');

        $tabs->add('create', [
            'title' => $this->translate('Create test'),
            'label' => $this->translate('Create'),
            'url'   => 'ktesting/testing/create'
        ]);

        $tabs->add('templates', [
            'title' => $this->translate('Show templates'),
            'label' => $this->translate('Templates'),
            'url'   => 'ktesting/templates'
        ]);

        return $tabs;
    }
}
