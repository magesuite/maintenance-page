<?php

declare(strict_types=1);

namespace MageSuite\MaintenancePage\Test\Integration\Service;

class ErrorPagesDeployerTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\MaintenancePage\Service\ErrorPagesDeployer $errorPagesDeployer;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->errorPagesDeployer = $objectManager->get(\MageSuite\MaintenancePage\Service\ErrorPagesDeployer::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture MageSuite_MaintenancePage::Test/Integration/_files/templates.php
     */
    public function testItReturnsCorrectTemplatePath(): void
    {
        $this->errorPagesDeployer->execute();

        $path = BP . '/pub/errors/';

        $this->assertFileExists($path . 'local_sample.xml');
        $this->assertDirectoryExists($path . 'custom');
        $this->assertFileExists($path . 'custom/index.html');
        $this->assertFileExists($path . 'custom/css/style.css');

        $this->assertEquals('Test error page', file_get_contents($path . 'custom/index.html'));
        $this->assertEquals('.body{ background: #000; }', file_get_contents($path . 'custom/css/style.css'));
    }
}
