<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Module\Ktesting\Model\Template;
use Icinga\Module\Ktesting\Web\Controller;
use Icinga\Module\Ktesting\Forms\EditTemplateForm;
use Icinga\Module\Ktesting\Common\Database;
use Icinga\Web\Notification;
use ipl\Html\Form;
use ipl\Web\Url;
use ipl\Stdlib\Filter;
use Exception;

class TemplateController extends Controller
{
    protected Template $template;

    public function init(): void
    {
        parent::init();

        try {
            /** @var Template $template */
            $template = Template::on(Database::connection())
                ->filter(Filter::equal('id', $this->params->getRequired('id')))
                ->first();

            if ($template === null) {
                throw new Exception('Template not found');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $this->template = $template;
    }

    public function editAction(): void
    {
        $this->addTitleTab($this->translate('Edit Template'));

        $form = EditTemplateForm::fromTemplate($this->template)
            ->on(EditTemplateForm::ON_SUCCESS, function (Form $form) {
                $pressedButton = $form->getPressedSubmitElement();
                if ($pressedButton && $pressedButton->getName() === 'remove') {
                    Notification::success($this->translate('Removed template successfully'));

                    $this->switchToSingleColumnLayout();
                } else {
                    Notification::success($this->translate('Updated template successfully'));

                    $this->closeModalAndRefreshRemainingViews(
                        Url::fromPath('ktesting/template', ['id' => $this->template->id])
                    );
                }
            })->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
