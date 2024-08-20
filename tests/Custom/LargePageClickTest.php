<?php

namespace Behat\Mink\Tests\Driver\Custom;

use Behat\Mink\Tests\Driver\TestCase;

class LargePageClickTest extends TestCase
{
    public function testLargePageClick(): void
    {
        $this->getSession()->visit($this->pathTo('/advanced_form.html'));
        // @todo Why is size attribute causing ElementClickIntercepted errors?
        $this->getSession()->executeScript('document.querySelector("input[name=\'first_name\']").setAttribute("size", 200);');

        // Add a large amount of br tags so that form elements are not in view.
        $this->makePageLong();

        $page = $this->getSession()->getPage();

        // Test select focus.
        $this->scrollToTop();
        $page->selectFieldOption('select_number', 'thirty');

        // Test radio button focus.
        $this->scrollToTop();
        $page->selectFieldOption('sex', 'm');

        // Test checkboxes focus.
        $this->scrollToTop();
        $page->uncheckField('mail_list');
        $this->scrollToTop();
        $page->checkField('agreement');

        // Test button focus and submit.
        $this->scrollToTop();
        $page->pressButton('Register');

        $expected = <<<EOF
array(
  agreement = `on`,
  email = `your@email.com`,
  first_name = `Firstname`,
  last_name = `Lastname`,
  notes = `original notes`,
  select_number = `30`,
  sex = `m`,
  submit = `Register`,
)
no file
EOF;

        $this->assertStringContainsString($expected, $page->getContent());
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
     * Scrolls to the top of the page.
     */
    private function scrollToTop(): void {
        $this->getSession()->executeScript('window.scrollTo(0, 0);');
    }

}
