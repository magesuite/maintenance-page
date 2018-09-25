<?php

namespace MageSuite\MaintenancePage\Test\Integration\Service;

class ErrorPagesDeployerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \MageSuite\MaintenancePage\Service\ErrorPagesDeployer
     */
    private $errorPagesDeployer;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->errorPagesDeployer = $this->objectManager->get(\MageSuite\MaintenancePage\Service\ErrorPagesDeployer::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture prepareTemplates
     */
    public function testItReturnsCorrectTemplatePath()
    {
        $this->errorPagesDeployer->execute();

        $path = BP . '/pub/errors/';

        $this->assertFileExists($path . 'local.xml');
        $this->assertDirectoryExists($path . 'custom');
        $this->assertFileExists($path . 'custom/index.html');
        $this->assertFileExists($path . 'custom/css/style.css');

        $this->assertEquals('Test error page', file_get_contents($path . 'custom/index.html'));
        $this->assertEquals('.body{ background: #000; }', file_get_contents($path . 'custom/css/style.css'));
    }

    public static function prepareTemplates()
    {
        require __DIR__ . '/../_files/templates.php';
    }

    public static function prepareTemplatesRollback()
    {
        require __DIR__ . '/../_files/templates_rollback.php';
    }

}