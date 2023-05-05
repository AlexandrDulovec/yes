<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class SignPresenter extends Nette\Application\UI\Presenter
{
    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->setHtmlAttribute('class', 'signInForm');
        $form->setMethod('POST');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte uživatelské jméno.')
            ->setHtmlAttribute('id', 'username');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte heslo.')
            ->setHtmlAttribute('id', 'password');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            $this->getUser()->login($data->username, $data->password);
            $this->redirect('Home:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné.');
        $this->redirect('Home:');
    }
}