<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Forms;

use Exception;
use GuzzleHttp\Psr7\UploadedFile;
use Icinga\Module\Ktesting\Common\Database;
use Icinga\Module\Ktesting\Model\TemplateTest;
use Icinga\Util\Json;
use ipl\Html\Attributes;
use ipl\Html\Html;
use ipl\Html\HtmlDocument;
use ipl\Sql\Select;
use ipl\Web\Compat\CompatForm;

class EditTemplateForm extends CompatForm
{
    protected $template;

    protected int $noTemplateTests;

    /**
     * Create a new form instance with the given template
     *
     * @param $template
     *
     * @return static
     */
    public static function fromTemplate($template): self
    {
        $form = new static();

        $form->template = $template;

        try {
            $res = Database::connection()->prepexec(
                (new Select())
                    ->columns(['test_kind', 'total_replicas', 'bad_replicas'])
                    ->from('template_test')
                    ->join('template', 'template.id=template_test.template_id')
                    ->where('template.id=?', $template->id)
            )->fetchAll();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $form->populate($template);

        foreach ($res as $index => $row) {
            $form->populate([
                "testKind-" . $index      => $row->test_kind,
                "totalReplicas-" . $index => $row->total_replicas,
                "badReplicas-" . $index   => $row->bad_replicas
            ]);
        }

        $form->noTemplateTests = count($res);

        return $form;
    }

    public function hasBeenSubmitted(): bool
    {
        return $this->hasBeenSent() && ($this->getPopulatedValue('submit') || $this->getPopulatedValue('remove'));
    }

    protected function assemble()
    {
        $updateBtn = $this->createElement(
            'submit',
            'submit',
            [
                'label' => $this->translate('Update Template')
            ]
        );
        $this->registerElement($updateBtn);
        $this->decorate($updateBtn);

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

        $this->addElement('text', 'name', [
            'label'       => $this->translate('Name'),
            'placeholder' => $this->translate('Template name'),
            'required'    => true
        ]);

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

        $this->addElement(
            'hidden',
            'numberOfAdditionalFields',
            [
                'type'  => 'hidden',
                'value' => $this->noTemplateTests
            ]
        );

        $noAddFields = $this->noTemplateTests;

        if ($this->getElement('addFields')->hasBeenPressed()) {
            $noAddFields = intval($this->getValue('numberOfAdditionalFields'));
            $noAddFields++;
            $this->getElement('numberOfAdditionalFields')->setValue($noAddFields);
        }

        if ($this->getElement('removeFields')->hasBeenPressed()) {
            $noAddFields = intval($this->getValue('numberOfAdditionalFields'));
            $noAddFields--;
            $this->getElement('numberOfAdditionalFields')->setValue($noAddFields);
        }

        if ($this->getElement('submit')->hasBeenPressed()) {
            $noAddFields = intval($this->getValue('numberOfAdditionalFields'));
        }

        for ($i = 1; $i < $noAddFields; $i++) {
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
                        ($noAddFields > 1) ? $removeBtn : null,
                        $addBtn,
                    ]
                )
            )
        );

//        $this->addElement('submit', 'submit', [
//            'label' => $this->template === null
//                ? $this->translate('Create Template')
//                : $this->translate('Update Template')
//        ]);

        $this->addHtml($updateBtn);

//        if ($this->template !== null) {
        $removeButton = $this->createElement('submit', 'remove', [
            'label'          => $this->translate('Remove Template'),
            'class'          => 'btn-remove',
            'formnovalidate' => true
        ]);
        $this->registerElement($removeButton);

        /** @var HtmlDocument $wrapper */
        $wrapper = $this->getElement('submit')->getWrapper();
        $wrapper->prepend($removeButton);
//        }
    }

    public function onSuccess()
    {
        try {
            Database::connection()->delete('template_test', ['template_id = ?' => $this->template->id]);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        if ($this->getPopulatedValue('remove')) {
            try {
                Database::connection()->delete('template', ['id = ?' => $this->template->id]);
            } catch (Exception $e) {
                die($e->getMessage());
            }
            return;
        }

        $values = $this->getValues();

        $db = Database::connection();
        try {
            $db->update('template', [
                'name'     => $values['name'],
                'modified' => time() * 1000
            ], ['id = ?' => $this->template->id]);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        for ($i = 0; ; $i++) {
            if (
                ! isset($values["testKind-$i"])
                || ! isset($values["totalReplicas-$i"])
                || ! isset($values["badReplicas-$i"])
            ) {
                break;
            }
            try {
                $db->insert('template_test', [
                    'template_id'    => $this->template->id,
                    'test_kind'      => $values["testKind-$i"],
                    'total_replicas' => $values["totalReplicas-$i"],
                    'bad_replicas'   => $values["badReplicas-$i"]
                ]);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }
}
