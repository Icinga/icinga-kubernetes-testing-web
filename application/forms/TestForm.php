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

//        $templates = Template::on(Database::connection())->execute();
        $options = [null => 'Please Choose'];

//        foreach ($templates as $template) {
//            $options[$template->name] = $template->name;
//        }

        $this->add(Html::tag('h2', 'General'));

        $this->addElement(
            'select',
            'template',
            [
                'label'   => $this->translate('Template'),
                'options' => $options,
                'class'   => 'autosubmit'
            ]
        );

//        $templateTests = TemplateTest::on(Database::connection())
//            ->filter(Filter::equal('template.name', $this->getPopulatedValue('template')))
//            ->execute();

        $baseCount = 1;
//        foreach ($templateTests as $_) {
//            $baseCount++;
//        }

//        $baseCount = $baseCount > 0 ? $baseCount : 1;

        $this->addElement(
            'hidden',
            'numberOfAdditionalFields',
            [
                'value' => $baseCount
            ]
        );

        $this->addElement(
            'select',
            'resourceType',
            [
                'label'   => $this->translate('Resource Type'),
                'options' => [
                    'deployment'  => 'Deployment',
                    'replicaset'  => 'ReplicaSet',
                    'statefulset' => 'StatefulSet',
                    'daemonset'   => 'DaemonSet',

                ],
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
            $resourceType = str_replace('set', 'Set', ucfirst($this->getValue('resourceType')));
        }

        $this->addElement(
            'input',
            'resourceName',
            [
                'type'     => 'text',
                'label'    => $this->translate(($resourceType ?? 'Deployment') . ' Name'),
                'required' => true,
            ]
        );

        $this->addElement(
            'textarea',
            'description',
            [
                'label' => $this->translate('Description'),
            ]
        );

        $this->addElement(
            'input',
            'expectedPods',
            [
                'type'     => 'number',
                'label'    => $this->translate('Expected Pods'),
                'required' => true,
                'min'      => 1,
            ]
        );

        $this->add(Html::tag('h2', 'Tests'));

        $noOfAddFields = $baseCount;

        if ($this->getElement('addFields')->hasBeenPressed()) {
            if ($noOfAddFields < $baseCount) {
                $noOfAddFields = $baseCount;
            } else {
                $noOfAddFields = (int)$this->getValue('numberOfAdditionalFields');
            }
            $noOfAddFields++;
            $this->getElement('numberOfAdditionalFields')->setValue($noOfAddFields);
        }

        if ($this->getElement('removeFields')->hasBeenPressed()) {
            $noOfAddFields = (int)$this->getValue('numberOfAdditionalFields');
            $noOfAddFields--;
            $this->getElement('numberOfAdditionalFields')->setValue($noOfAddFields);
        }

        if ($this->hasBeenSubmitted()) {
            $noOfAddFields = (int)$this->getValue('numberOfAdditionalFields');
        }

        for ($i = 0; $i < $noOfAddFields; $i++) {
            $testKind = $this->createElement(
                'select',
                "testKind-$i",
                [
                    'required' => true,
                    'options'  => [
                        null     => 'Please Choose',
                        'cpu'    => 'cpu',
                        'memory' => 'memory',
                    ]
                ]
            );
            $this->registerElement($testKind);

            $testPercentage = $this->createElement(
                'input',
                "testPercentage-$i",
                [
                    'type'     => 'number',
                    'required' => true,
                    'value'    => '',
                    'min'      => 1,
                    'max'      => 100,
                ]
            );
            $this->registerElement($testPercentage);

            $this->addHtml(
                Html::tag(
                    'div',
                    Attributes::create(['class' => 'control-group form-controls']),
                    [
                        Html::tag(
                            'div',
                            Attributes::create(['class' => 'control-label-group']),
                        ),
                        $testKind,
                        $testPercentage,
                        Html::tag('p', '%')
                    ]
                )
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

//        $counter = 0;
//        // TODO set default values for template tests
//        foreach ($templateTests as $test) {
//            $this->getElement("testKind-$counter")->setValue($test->test_kind);
//            $this->getElement("totalReplicas-$counter")->setValue($test->total_replicas);
//            $this->getElement("badReplicas-$counter")->setValue($test->bad_replicas);
//            $counter++;
//        }
    }
}
