<?php

/* Icinga for Kubernetes Web | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Forms;

use Icinga\Data\ResourceFactory;
use ipl\Web\Compat\CompatForm;

class ApiConfigForm extends CompatForm
{
    protected function assemble()
    {
        $this->addElement(
            'input',
            'clusterIp',
            [
                'label'    => $this->translate('Cluster IP'),
                'disable'  => [''],
                'required' => true,
                'value'    => ''
            ]
        );

        $this->addElement(
            'input',
            'apiPort',
            [
                'label'    => $this->translate('API Port'),
                'disable'  => [''],
                'required' => true,
                'value'    => ''
            ]
        );

        $this->addElement(
            'input',
            'dbPort',
            [
                'label'    => $this->translate('DB Port'),
                'disable'  => [''],
                'required' => true,
                'value'    => ''
            ]
        );

        $this->addElement(
            'submit',
            'submit',
            [
                'label' => $this->translate('Save Changes')
            ]
        );
    }
}
