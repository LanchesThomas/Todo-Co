<?php
// tests/Controller/UserControllerTest.php
namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends TestCase
{
    private EntityManagerInterface $em;
    private UserRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em   = $this->createMock(EntityManagerInterface::class);
        $this->repo = $this->createMock(UserRepository::class);
    }

    public function testListRendersUsers(): void
    {
        $users = [new User(), new User()];
        $controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['render'])
            ->getMock();

        $this->repo->expects($this->once())
            ->method('findAll')
            ->willReturn($users);

        $controller->expects($this->once())
            ->method('render')
            ->with('user/list.html.twig', ['users' => $users])
            ->willReturn(new Response());

        $response = $controller->list();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCreateRendersFormWhenNotSubmitted(): void
    {
        $controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['createForm', 'render'])
            ->getMock();

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isSubmitted')->willReturn(false);
        $form->expects($this->never())->method('isValid');
        $view = $this->createMock(FormView::class);
        $form->expects($this->once())->method('createView')->willReturn($view);

        $controller->expects($this->once())
            ->method('createForm')
            ->with('App\\Form\\UserType', $this->isInstanceOf(User::class))
            ->willReturn($form);

        $controller->expects($this->once())
            ->method('render')
            ->with('user/create.html.twig', ['form' => $view])
            ->willReturn(new Response());

        $request = new Request();
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $response = $controller->create($request, $hasher);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCreatePersistsAndRedirectsWhenValid(): void
    {
        $controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['createForm', 'addFlash', 'redirectToRoute'])
            ->getMock();

        // Prépare le form stub
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        // roles field
        $roleField = $this->createMock(FormInterface::class);
        $roleField->expects($this->once())->method('getData')->willReturn('ROLE_USER');
        $form->expects($this->once())->method('get')->with('roles')->willReturn($roleField);

        // Simule que le formulaire a rempli le mot de passe
        $controller->expects($this->once())
            ->method('createForm')
            ->with(
                'App\\Form\\UserType',
                $this->callback(function(User $u) {
                    $u->setPassword('plainPass');
                    return true;
                })
            )
            ->willReturn($form);

        // Password hasher
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), 'plainPass')
            ->willReturn('hashedPass');

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('success', "L'utilisateur a bien été ajouté.");

        $redirect = new RedirectResponse('/users');
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('user_list')
            ->willReturn($redirect);

        $request = new Request();
        $response = $controller->create($request, $hasher);
        $this->assertSame($redirect, $response);
    }
}
