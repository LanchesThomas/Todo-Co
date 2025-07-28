<?php
// tests/Controller/SecurityControllerTest.php
namespace App\Tests\Controller;

use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityControllerTest extends TestCase
{
    public function testLoginRedirectsToHomeWhenAuthenticated(): void
    {
        $controller = $this->getMockBuilder(SecurityController::class)
            ->onlyMethods(['getUser', 'redirectToRoute'])
            ->getMock();

        // Retourne un stub conforme à UserInterface
        $userStub = $this->createMock(UserInterface::class);
        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($userStub);

        $fakeRedirect = new RedirectResponse('/');
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('/')
            ->willReturn($fakeRedirect);

        $response = $controller->login($this->createMock(AuthenticationUtils::class));
        $this->assertSame($fakeRedirect, $response);
    }

    public function testLoginRendersFormWhenNotAuthenticated(): void
    {
        $controller = $this->getMockBuilder(SecurityController::class)
            ->onlyMethods(['getUser', 'render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        // Stub AuthenticationUtils retournant une AuthenticationException
        $authUtils = $this->createMock(AuthenticationUtils::class);
        $authException = new AuthenticationException('Bad credentials');
        $authUtils->expects($this->once())
            ->method('getLastAuthenticationError')
            ->willReturn($authException);
        $authUtils->expects($this->once())
            ->method('getLastUsername')
            ->willReturn('john.doe');

        $fakeResponse = new Response('<form>login</form>', 200);
        $controller->expects($this->once())
            ->method('render')
            ->with('security/login.html.twig', [
                'last_username' => 'john.doe',
                'error' => $authException,
            ])
            ->willReturn($fakeResponse);

        $response = $controller->login($authUtils);
        $this->assertSame($fakeResponse, $response);
    }

    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank');

        $controller = new SecurityController();
        $controller->logout();
    }
}
