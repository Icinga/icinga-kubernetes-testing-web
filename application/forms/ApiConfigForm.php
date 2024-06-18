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
            'clusterip',
            [
                'label'    => $this->translate('Cluster IP'),
                'disable'  => [''],
                'required' => true,
                'value'    => ''
            ]
        );

        $this->addElement(
            'input',
            'port',
            [
                'label'    => $this->translate('Port'),
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
