<?php

namespace Behat\Mink\Tests\Driver\Custom;

use Behat\Mink\Tests\Driver\TestCase;
use Composer\InstalledVersions;

class RemoteFileUploadTest extends TestCase
{
    public function testRemoteFileUpload(): void
    {
        $this->getSession()->visit($this->pathTo('/advanced_form.html'));

        $webAssert = $this->getAssertSession();
        $page = $this->getSession()->getPage();

        $about = $webAssert->fieldExists('about');
        // Place a file outside of the directories mapped to the selenium
        // server.
        $path = sys_get_temp_dir() . '/some_file.txt';
        copy(InstalledVersions::getInstallPath('mink/driver-testsuite') . '/web-fixtures/some_file.txt', $path);
        $about->attachFile($path);
        unlink(sys_get_temp_dir() . '/some_file.txt');

        $button = $page->findButton('Register');
        $this->assertNotNull($button);
        $button->press();

        if ($this->safePageWait(5000, 'document.title === "Advanced form save"')) {
            $out = <<<'OUT'
some_file.txt
1 uploaded file
OUT;
            $this->assertStringContainsString($out, $page->getContent());
        }
        else {
            $this->fail('Failed to submit form');
        }
    }

    protected function tearDown(): void
    {
        if (file_exists(sys_get_temp_dir() . '/some_file.txt')) {
            unlink(sys_get_temp_dir() . '/some_file.txt');
        }
    }

}
