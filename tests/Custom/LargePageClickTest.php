<?php

namespace Behat\Mink\Tests\Driver\Custom;

use Behat\Mink\Tests\Driver\TestCase;

class LargePageClickTest extends TestCase
{
    public function testLargePageClick(): void
    {
        $this->getSession()->visit($this->pathTo('/multi_input_form.html'));

        // Add a large amount of br tags so that the button is not in view.
        $this->makePageLong();

        $page = $this->getSession()->getPage();
        $page->pressButton('Register');
        $this->assertStringContainsString('no file', $page->getContent());
    }

    public function testDragDrop(): void
    {
        $this->getSession()->visit($this->pathTo('/js_test.html'));
        // Add a large amount of br tags so that the draggable area is not in
        // view.
        $this->makePageLong();

        $webAssert = $this->getAssertSession();

        $draggable = $webAssert->elementExists('css', '#draggable');
        $droppable = $webAssert->elementExists('css', '#droppable');

        $draggable->dragTo($droppable);
        $this->assertSame('Dropped left!', $webAssert->elementExists('css', 'p', $droppable)->getText());
    }

    public function testClickWithBottomOverlay(): void
    {
        $this->getSession()->visit($this->pathTo('/multi_input_form.html'));

        // Simulate a sticky bottom overlay (e.g. a cookie banner).
        $this->getSession()->executeScript(<<<JS
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;bottom:0;left:0;width:100%;height:100px;background:red;z-index:9999;';
            document.body.appendChild(overlay);
        JS);

        // Push the button out of view and add content below it too, so the
        // browser has room to scroll and center the button in the viewport.
        $this->makePageLong();
        $this->makePageLongAfter();

        $page = $this->getSession()->getPage();
        $page->pressButton('Register');
        $this->assertStringContainsString('no file', $page->getContent());
    }

    /**
     * Makes the page really long by inserting br tags at the top.
     */
    private function makePageLong(): void {
        $large_page = str_repeat('<br />', 2000);
        $script = <<<JS
            const p = document.createElement("div");
            p.innerHTML = "$large_page";
            document.body.insertBefore(p, document.body.firstChild);
        JS;
        $this->getSession()->executeScript($script);
    }

    /**
     * Makes the page really long by appending br tags at the bottom.
     */
    private function makePageLongAfter(): void {
        $large_page = str_repeat('<br />', 2000);
        $script = <<<JS
            const p = document.createElement("div");
            p.innerHTML = "$large_page";
            document.body.appendChild(p);
        JS;
        $this->getSession()->executeScript($script);
    }

}
