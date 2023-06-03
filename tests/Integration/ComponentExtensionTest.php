<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ComponentExtensionTest extends KernelTestCase
{
    public function testCanRenderComponent(): void
    {
        $output = $this->renderComponent('component_a', [
            'propA' => 'prop a value',
            'propB' => 'prop b value',
        ]);

        $this->assertStringContainsString('propA: prop a value', $output);
        $this->assertStringContainsString('propB: prop b value', $output);
        $this->assertStringContainsString('service: service a value', $output);
    }

    public function testCanRenderTheSameComponentMultipleTimes(): void
    {
        $output = self::getContainer()->get(Environment::class)->render('multi_render.html.twig');

        $this->assertStringContainsString('propA: prop a value 1', $output);
        $this->assertStringContainsString('propB: prop b value 1', $output);
        $this->assertStringContainsString('propA: prop a value 2', $output);
        $this->assertStringContainsString('propB: prop b value 2', $output);
        $this->assertStringContainsString('b value: pre-mount b value 1', $output);
        $this->assertStringContainsString('post value: value', $output);
        $this->assertStringContainsString('service: service a value', $output);
    }

    public function testCanRenderComponentWithMoreAdvancedTwigExpressions(): void
    {
        $output = self::getContainer()->get(Environment::class)->render('flexible_component_attributes.html.twig');

        $this->assertStringContainsString('propA: A1', $output);
        $this->assertStringContainsString('propB: B1', $output);
        $this->assertStringContainsString('propA: A2', $output);
        $this->assertStringContainsString('propB: B2', $output);
        $this->assertStringContainsString('propA: A3', $output);
        $this->assertStringContainsString('propB: B3', $output);
        $this->assertStringContainsString('propA: A4', $output);
        $this->assertStringContainsString('propB: B4', $output);
        $this->assertStringContainsString('service: service a value', $output);
    }

    public function testCanNotRenderComponentWithInvalidExpressions(): void
    {
        $this->expectException(\TypeError::class);
        self::getContainer()->get(Environment::class)->render('invalid_flexible_component.html.twig');
    }

    public function testCanCustomizeTemplateWithAttribute(): void
    {
        $output = $this->renderComponent('component_b', ['value' => 'b value 1']);

        $this->assertStringContainsString('Custom template 1', $output);
    }

    public function testCanCustomizeTemplateWithServiceTag(): void
    {
        $output = $this->renderComponent('component_d', ['value' => 'b value 1']);

        $this->assertStringContainsString('Custom template 2', $output);
    }

    public function testCanRenderComponentWithAttributes(): void
    {
        $output = $this->renderComponent('with_attributes', [
            'prop' => 'prop value 1',
            'class' => 'bar',
            'style' => 'color:red;',
            'value' => '',
            'autofocus' => true,
        ]);

        $this->assertStringContainsString('Component Content (prop value 1)', $output);
        $this->assertStringContainsString('<button class="foo bar" type="button" style="color:red;" value="" autofocus>', $output);

        $output = $this->renderComponent('with_attributes', [
            'prop' => 'prop value 2',
            'attributes' => ['class' => 'baz'],
            'type' => 'submit',
            'style' => 'color:red;',
        ]);

        $this->assertStringContainsString('Component Content (prop value 2)', $output);
        $this->assertStringContainsString('<button class="foo baz" type="submit" style="color:red;">', $output);
    }

    public function testCanSetCustomAttributesVariable(): void
    {
        $output = $this->renderComponent('custom_attributes', ['class' => 'from-custom']);

        $this->assertStringContainsString('<div class="from-custom"></div>', $output);
    }

    public function testRenderComponentWithExposedVariables(): void
    {
        $output = $this->renderComponent('with_exposed_variables');

        $this->assertStringContainsString('Prop1: prop1 value', $output);
        $this->assertStringContainsString('Prop2: prop2 value', $output);
        $this->assertStringContainsString('Prop3: prop3 value', $output);
        $this->assertStringContainsString('Method1: method1 value', $output);
        $this->assertStringContainsString('Method2: method2 value', $output);
        $this->assertStringContainsString('customMethod: customMethod value', $output);
    }

    public function testCanUseComputedMethods(): void
    {
        $output = $this->renderComponent('computed_component');

        $this->assertStringContainsString('countDirect1: 1', $output);
        $this->assertStringContainsString('countDirect2: 2', $output);
        $this->assertStringContainsString('countComputed1: 3', $output);
        $this->assertStringContainsString('countComputed2: 3', $output);
        $this->assertStringContainsString('countComputed3: 3', $output);
        $this->assertStringContainsString('propDirect: value', $output);
        $this->assertStringContainsString('propComputed: value', $output);
    }

    public function testCanDisableExposingPublicProps(): void
    {
        $output = $this->renderComponent('no_public_props');

        $this->assertStringContainsString('NoPublicProp1: default', $output);
    }

    public function testCanRenderEmbeddedComponent(): void
    {
        $output = self::getContainer()->get(Environment::class)->render('embedded_component.html.twig');

        $this->assertStringContainsString('<caption>data table</caption>', $output);
        $this->assertStringContainsString('custom th (key)', $output);
        $this->assertStringContainsString('custom td (1)', $output);
    }

    public function testCanPassBlocksToChildEmbeddedComponent(): void
    {
        $output = self::getContainer()->get(Environment::class)->render('embedded_component_passthrough_blocks.html.twig');

        // variable available from outside context + content overriding default content two levels deep (aka passthrough)
        $this->assertStringContainsString('<div class="divComponent">Hello Fabien!</div>', $output);
        // usage of parent function
        $this->assertStringContainsString('The Generic Element could have some default content, although it does not make sense in this example. It is accessible via the {{ parent() }} function.', $output);
        // access to embedded component's properties from within overriding content block
        $this->assertStringContainsString('Yeah, the Generic Element\'s property "id" is "symfonyIsAwesome"', $output);
        // access to embedded component's functions from within overriding content block
        $this->assertStringContainsString('And the result of a function via `this` works too: calling GenericElement.', $output);
        // access to component's properties from within an embedded component
        $this->assertStringContainsString('I can also access DivComponent\'s properties just like those of Generic Element. I know DivComponent\'s name is foo.', $output);
        // access to component's properties from within an embedded component
        $this->assertStringContainsString("But note that I can not access the scope of the template embedding this component.\n    I can't tell you what \"name=\" should be, since this content is only used when you're NOT using embedding components\n    (aka a self closing twig tag, or a {{ component }}", $output);
        // wrapping component overriding content block with another default block
        $this->assertStringContainsString('We can even do something crazy...', $output);
        // wrapping component overriding content block three levels deep (aka passthrough)
        $this->assertStringContainsString('And pass the content along and along and along.', $output);
        // access to embedded component's properties from within overriding content block even at this level
        $this->assertStringContainsString('Btw even I can access the final block\'s context. I know that the id is symfonyIsAwesome as well!', $output);
        // access to second embedded component's properties from within overriding block
        $this->assertStringContainsString('And of course since DivComponentWrapper\'s content is rendered inside of a DivComponent element, I also know DivComponent\'s properties. Its name is foo!', $output);
        // access to first component's properties from within overriding block
        $this->assertStringContainsString('Apart from the obvious access to DivComponentWrapper\'s properties, like its property "name": bar.', $output);
        // this refers to the Component where the block is eventually shown
        $this->assertStringContainsString("The less obvious thing is that even at this level \"this\" refers to the component where the content block is used, i.e. the Generic Element.\n    Therefore, functions through this will be calling GenericElement.", $output);
    }

    public function testComponentWithNamespace(): void
    {
        $output = $this->renderComponent('foo:bar:baz');

        $this->assertStringContainsString('Content...', $output);
    }

    private function renderComponent(string $name, array $data = []): string
    {
        return self::getContainer()->get(Environment::class)->render('render_component.html.twig', [
            'name' => $name,
            'data' => $data,
        ]);
    }
}
