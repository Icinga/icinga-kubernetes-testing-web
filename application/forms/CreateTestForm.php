<?php

namespace Icinga\Module\Ktesting\Forms;

use ipl\Html\Attributes;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\TextElement;
use ipl\Web\Compat\CompatForm;
use ipl\Html\Html;

class CreateTestForm extends CompatForm
{
    protected function assemble(): void
    {
        $submitBtn = $this->createElement(
            'submit',
            'submit',
            [
                'label' => $this->translate('Create Test')
            ]
        );
        $this->registerElement($submitBtn);

        $addBtn = $this->createElement(
            'submit',
            'addFields',
            [
                'label' => '+',
                'formnovalidate' => true
            ]
        );
        $this->registerElement($addBtn);

        $removeBtn = $this->createElement(
            'submit',
            'removeFields',
            [
                'label' => '-',
                'formnovalidate' => true
            ]
        );
        $this->registerElement($removeBtn);

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

        $this->addElement(
            'input',
            'templateName',
            [
                'type' => 'text',
                'label' => $this->translate('Template Name (optional)'),
                'required' => false,
                'value' => ''
            ]
        );

        $this->addHtml(Html::tag('br'));

        $this->addElement(
            'select',
            'testKind',
            [
                'label' => $this->translate('Test Kind'),
                'required' => true,
                'options' => [
                    null => 'Please Choose',
                    'cpu' => 'cpu',
                    'memory' => 'memory',
                ]
            ]
        );

        $this->addElement(
            'input',
            'totalReplicas',
            [
                'type' => 'number',
                'label' => $this->translate('Total Replicas'),
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

        $noOfAddFields = 0;

        if ($this->getElement('addFields')->hasBeenPressed()) {
            $noOfAddFields = intval($this->getValue('numberOfAdditionalFields'));
            $noOfAddFields++;
            $this->getElement('numberOfAdditionalFields')->setValue($noOfAddFields);
        }

        if ($this->getElement('removeFields')->hasBeenPressed()) {
            $noOfAddFields = intval($this->getValue('numberOfAdditionalFields'));
            $noOfAddFields--;
            $this->getElement('numberOfAdditionalFields')->setValue($noOfAddFields);
        }

        if ($this->getElement('submit')->hasBeenPressed()) {
            $noOfAddFields = intval($this->getValue('numberOfAdditionalFields'));
        }

        for ($i = 0; $i < $noOfAddFields; $i++) {
            $this->addHtml(Html::tag('br'));

            $this->addElement(
                'select',
                "testKind-$i",
                [
                    'label' => $this->translate('Test Kind'),
                    'required' => true,
                    'options' => [
                        null => 'Please Choose',
                        'cpu' => 'cpu',
                        'memory' => 'memory',
                    ]
                ]
            );

            $this->addElement(
                'input',
                "totalReplicas-$i",
                [
                    'type' => 'number',
                    'label' => $this->translate('Total Replicas'),
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

        $this->addHtml(
            Html::tag(
                'div',
                Attributes::create(['class' => 'control-group form-controls']),
                Html::tag(
                    'div',
                    Attributes::create(),
                    [
                        ($noOfAddFields > 0) ? $removeBtn : null,
                        $addBtn,
                    ]
                )
            )
        );

        $this->addElement($submitBtn);
    }
}
