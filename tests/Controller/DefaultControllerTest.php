<?php
// tests/Controller/DefaultControllerTest.php
namespace App\Tests\Controller;

use App\Controller\DefaultController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultControllerTest extends TestCase
{
    public function testIndexRedirectsToLoginWhenNotAuthenticated(): void
    {
        // On ne mocke que getUser() et redirectToRoute()
        $controller = $this->getMockBuilder(DefaultController::class)
                           ->onlyMethods(['getUser', 'redirectToRoute'])
                           ->getMock();

        // getUser() renvoie null
        $controller->expects($this->once())
                   ->method('getUser')
                   ->willReturn(null);

        // Et redirectToRoute() doit renvoyer un RedirectResponse
        $fakeRedirect = new RedirectResponse('/login');
        $controller->expects($this->once())
                   ->method('redirectToRoute')
                   ->with('/login')
                   ->willReturn($fakeRedirect);

        $response = $controller->index();

        $this->assertSame($fakeRedirect, $response);
    }

    public function testIndexRendersHomepageWhenAuthenticated(): void
    {
        // On mocke getUser() et render()
        $controller = $this->getMockBuilder(DefaultController::class)
                           ->onlyMethods(['getUser', 'render'])
                           ->getMock();

        // getUser() renvoie un stub implémentant UserInterface
        $stubUser = $this->createMock(UserInterface::class);
        $controller->expects($this->once())
                   ->method('getUser')
                   ->willReturn($stubUser);

        // render() doit renvoyer un vrai Response 200
        $fakeResponse = new Response('<html>OK</html>', 200);
        $controller->expects($this->once())
                   ->method('render')
                   ->with('default/index.html.twig')
                   ->willReturn($fakeResponse);

        $response = $controller->index();

        $this->assertSame($fakeResponse, $response);
    }
}
