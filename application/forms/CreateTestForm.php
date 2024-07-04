<?php

namespace Icinga\Module\Ktesting\Forms;

use ipl\Html\Contract\FormSubmitElement;
use ipl\Web\Compat\CompatForm;
use ipl\Html\Html;

class CreateTestForm extends CompatForm
{
    protected function assemble(): void
    {
        $submitButton = $this->createElement(
            'submit',
            'submit',
            [
                'label' => $this->translate('Create Test')
            ]
        );
        $this->registerElement($submitButton);

        $addButton = $this->createElement(
            'submit',
            'addFields',
            [
                'label' => '+',
                'formnovalidate' => true
            ]
        );
        $this->registerElement($addButton);

        $this->addElement(
            'input',
            'deploymentName',
            [
                'type' => 'text',
                'label' => $this->translate('Deployment Name'),
                'required' => true,
                'value' => ''
            ]
        );

        $this->addHtml(Html::tag('br'));

        $this->addElement(
            'input',
            'testKind',
            [
                'type' => 'text',
                'label' => $this->translate('Test Kind'),
                'required' => true,
                'value' => ''
            ]
        );

        $this->addElement(
            'input',
            'goodReplicas',
            [
                'type' => 'number',
                'label' => $this->translate('Good Replicas'),
                'required' => true,
                'value' => ''
            ]
        );

        $this->addElement(
            'input',
            'badReplicas',
            [
                'type' => 'number',
                'label' => $this->translate('Bad Replicas'),
                'required' => true,
                'value' => ''
            ]
        );

        $this->addElement(
            'hidden',
            'numberOfAdditionalFields',
            [
                'type' => 'hidden',
                'value' => 0
            ]
        );

        $numberOfAdditionalFields = 0;

        if ($this->getElement('addFields')->hasBeenPressed()) {
            $numberOfAdditionalFields = intval($this->getValue('numberOfAdditionalFields'));
            $numberOfAdditionalFields++;
            $this->getElement('numberOfAdditionalFields')->setValue($numberOfAdditionalFields);
        }

        if ($this->getElement('submit')->hasBeenPressed()) {
            $numberOfAdditionalFields = intval($this->getValue('numberOfAdditionalFields'));
        }

        for ($i = 0; $i < $numberOfAdditionalFields; $i++) {
            $this->addHtml(Html::tag('br'));

            $this->addElement(
                'input',
                "testKind-$i",
                [
                    'type' => 'text',
                    'label' => $this->translate('Test Kind'),
                    'required' => true,
                    'value' => ''
                ]
            );

            $this->addElement(
                'input',
                "goodReplicas-$i",
                [
                    'type' => 'number',
                    'label' => $this->translate('Good Replicas'),
                    'required' => true,
                    'value' => ''
                ]
            );

            $this->addElement(
                'input',
                "badReplicas-$i",
                [
                    'type' => 'number',
                    'label' => $this->translate('Bad Replicas'),
                    'required' => true,
                    'value' => ''
                ]
            );
        }

        $this->addElement($addButton);
        $this->addElement($submitButton);
    }
}
