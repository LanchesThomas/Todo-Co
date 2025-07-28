<?php
// tests/Form/UserTypeTest.php
namespace App\Tests\Form;

use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserTypeTest extends TypeTestCase
{
    public function testFormFieldsExist(): void
    {
        $form = $this->factory->create(UserType::class);
        $children = $form->all();

        $this->assertArrayHasKey('username', $children);
        $this->assertArrayHasKey('password', $children);
        $this->assertArrayHasKey('email', $children);
        $this->assertArrayHasKey('roles', $children);

        $this->assertInstanceOf(TextType::class, $form->get('username')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(RepeatedType::class, $form->get('password')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(EmailType::class, $form->get('email')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(ChoiceType::class, $form->get('roles')->getConfig()->getType()->getInnerType());
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'username' => 'alice',
            'password' => [
                'first' => 'secret123',
                'second' => 'secret123',
            ],
            'email' => 'alice@example.com',
            'roles' => 'ROLE_ADMIN',
        ];

        $form = $this->factory->create(UserType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('alice', $form->get('username')->getData());
        $this->assertSame('alice@example.com', $form->get('email')->getData());
        $this->assertSame('ROLE_ADMIN', $form->get('roles')->getData());

        $view = $form->createView();
        $viewChildren = array_keys($view->children);
        $this->assertEquals(['username', 'password', 'email', 'roles'], $viewChildren);
    }
}
