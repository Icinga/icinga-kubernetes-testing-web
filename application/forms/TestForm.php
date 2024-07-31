<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Forms;

use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Model\Template;
use Icinga\Module\Ktesting\Model\TemplateTest;
use Icinga\Module\Kubernetes\Web\Data;
use ipl\Html\Attributes;
use ipl\Orm\ResultSet;
use ipl\Stdlib\Filter;
use ipl\Web\Compat\CompatForm;
use ipl\Html\Html;

class TestForm extends CompatForm
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
                'label'          => '+',
                'formnovalidate' => true
            ]
        );
        $this->registerElement($addBtn);

        $removeBtn = $this->createElement(
            'submit',
            'removeFields',
            [
                'label'          => '-',
                'formnovalidate' => true
            ]
        );
        $this->registerElement($removeBtn);

        $templates = Template::on(Database::connection())->filter(Filter::all())->execute();
        $options = [null => 'Please Choose'];

        foreach ($templates as $template) {
            $options[$template->name] = $template->name;
        }

        $this->addElement(
            'select',
            'template',
            [
                'label'   => $this->translate('Template'),
                'options' => $options,
                'class'   => 'autosubmit'
            ]
        );

        if (
            $this->hasBeenSent()
            && $this->getElement('template')->isValid()
            && ! $this->hasBeenSubmitted()
            && ! $this->getElement('addFields')->hasBeenPressed()
            && ! $this->getElement('removeFields')->hasBeenPressed()
        )
        {
            $this->clearPopulatedValue('numberOfAdditionalFields');
        }

        $templateTests = TemplateTest::on(Database::connection())
            ->filter(Filter::equal('template.name', $this->getPopulatedValue('template')))
            ->execute();

        $baseCount = 0;
        foreach ($templateTests as $_) {
            $baseCount++;
        }

        $baseCount = $baseCount > 0 ? $baseCount : 1;

        $this->addElement(
            'hidden',
            'numberOfAdditionalFields',
            [
                'value' => $baseCount
            ]
        );

        $this->addElement(
            'input',
            'deploymentName',
            [
                'type'     => 'text',
                'label'    => $this->translate('Deployment Name'),
                'required' => true,
                'value'    => ''
            ]
        );

        $this->addHtml(Html::tag('br'));

        $this->addElement(
            'select',
            'testKind-0',
            [
                'label'    => $this->translate('Test Kind'),
                'required' => true,
                'options'  => [
                    null     => 'Please Choose',
                    'cpu'    => 'cpu',
                    'memory' => 'memory',
                ]
            ]
        );

        $this->addElement(
            'input',
            'totalReplicas-0',
            [
                'type'     => 'number',
                'label'    => $this->translate('Total Replicas'),
                'required' => true,
                'value'    => ''
            ]
        );

        $this->addElement(
            'input',
            'badReplicas-0',
            [
                'type'     => 'number',
                'label'    => $this->translate('Bad Replicas'),
                'required' => true,
                'value'    => ''
            ]
        );

        $noOfAddFields = $baseCount;

        if ($this->getElement('addFields')->hasBeenPressed()) {
            if ($noOfAddFields < $baseCount) {
                $noOfAddFields = $baseCount;
            } else {
                $noOfAddFields = (int) $this->getValue('numberOfAdditionalFields');
            }
            $noOfAddFields++;
            $this->getElement('numberOfAdditionalFields')->setValue($noOfAddFields);
        }

        if ($this->getElement('removeFields')->hasBeenPressed()) {
            $noOfAddFields = (int) $this->getValue('numberOfAdditionalFields');
            $noOfAddFields--;
            $this->getElement('numberOfAdditionalFields')->setValue($noOfAddFields);
        }

        if ($this->hasBeenSubmitted()) {
            $noOfAddFields = (int) $this->getValue('numberOfAdditionalFields');
        }

        for ($i = 1; $i < $noOfAddFields; $i++) {
            $this->addHtml(Html::tag('br'));

            $this->addElement(
                'select',
                "testKind-$i",
                [
                    'label'    => $this->translate('Test Kind'),
                    'required' => true,
                    'options'  => [
                        null     => 'Please Choose',
                        'cpu'    => 'cpu',
                        'memory' => 'memory',
                    ]
                ]
            );

            $this->addElement(
                'input',
                "totalReplicas-$i",
                [
                    'type'     => 'number',
                    'label'    => $this->translate('Total Replicas'),
                    'required' => true,
                    'value'    => ''
                ]
            );

            $this->addElement(
                'input',
                "badReplicas-$i",
                [
                    'type'     => 'number',
                    'label'    => $this->translate('Bad Replicas'),
                    'required' => true,
                    'value'    => ''
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
                        ($noOfAddFields > $baseCount) ? $removeBtn : null,
                        $addBtn,
                    ]
                )
            )
        );

        $this->addElement($submitBtn);

        $counter = 0;
        // TODO set default values for template tests
        foreach ($templateTests as $test) {
            $this->getElement("testKind-$counter")->setValue($test->test_kind);
            $this->getElement("totalReplicas-$counter")->setValue($test->total_replicas);
            $this->getElement("badReplicas-$counter")->setValue($test->bad_replicas);
            $counter++;
        }
    }
}
