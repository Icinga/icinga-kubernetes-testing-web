<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Forms;

use ipl\Web\Widget\Icon;

class DeleteForm extends CommandForm
{
    protected $defaultAttributes = ['class' => 'inline'];

    public function __construct()
    {
    }

    protected function assembleElements(): void
    {
    }

    protected function assembleSubmitButton(): void
    {
        $this->addElement(
            'submitButton',
            'btn_submit',
            [
                'class' => ['link-button'],
                'label' => [
                    new Icon('trash'),
                    t('Delete')
                ],
                'title' => t('Delete test')
            ]
        );
    }
}
