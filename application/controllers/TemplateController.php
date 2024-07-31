<?php

/* Icinga for Kubernetes Testing Web | (c) 2024 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Ktesting\Controllers;

use Icinga\Module\Ktesting\Common\Links;
use Icinga\Module\Ktesting\Model\Template;
use Icinga\Module\Ktesting\Web\Controller;
use Icinga\Module\Ktesting\Forms\TemplateForm;
use Icinga\Module\Ktesting\Common\Database;
use Icinga\Web\Notification;
use ipl\Stdlib\Filter;
use Exception;

class TemplateController extends Controller
{
    protected Template $template;

    public function createAction(): void
    {
        $this->addTitleTab($this->translate('Create Template'));

        $form = (new TemplateForm())
            ->on(TemplateForm::ON_SUCCESS, function (TemplateForm $form) {
                Notification::success($this->translate('Created template successfully'));

                $this->sendExtraUpdates(['#col1']);
                $this->redirectNow(Links::templateUpdate($form->getTemplate()));

            })->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }

    public function updateAction(): void
    {

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

        $this->addTitleTab($this->translate('Update Template'));

        $form = TemplateForm::fromTemplate($template)
            ->on(TemplateForm::ON_SUCCESS, function (TemplateForm $form) use ($template) {
                $pressedButton = $form->getPressedSubmitElement();
                if ($pressedButton && $pressedButton->getName() === 'remove') {
                    Notification::success($this->translate('Removed template successfully'));

                    $this->switchToSingleColumnLayout();
                } else {
                    Notification::success($this->translate('Updated template successfully'));

                    $this->closeModalAndRefreshRemainingViews(
                        Links::templateUpdate($template)
                    );
                }
            })->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
