<?php
// tests/Form/TaskTypeTest.php
namespace App\Tests\Form;

use App\Form\TaskType;
use Symfony\Component\Form\Test\TypeTestCase;

class TaskTypeTest extends TypeTestCase
{
    public function testBuildForm(): void
    {
        // Create the form of type TaskType
        $form = $this->factory->create(TaskType::class);

        // The form should contain exactly 'title' and 'content' fields
        $children = $form->all();
        $this->assertArrayHasKey('title', $children);
        $this->assertArrayHasKey('content', $children);
        $this->assertCount(2, $children);
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Test Task',
            'content' => 'This is a test task content.',
        ];

        // Submit the data to the form directly
        $form = $this->factory->create(TaskType::class);
        $form->submit($formData);

        // Ensure form is synchronized
        $this->assertTrue($form->isSynchronized());

        // Check that the form data matches the input
        $this->assertSame('Test Task', $form->get('title')->getData());
        $this->assertSame('This is a test task content.', $form->get('content')->getData());

        // Check that the form view has the correct children
        $view = $form->createView();
        $viewChildren = array_keys($view->children);
        $this->assertEquals(['title', 'content'], $viewChildren);
    }
}
