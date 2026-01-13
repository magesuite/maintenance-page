<?php

declare(strict_types=1);

namespace MageSuite\MaintenancePage\Service;

class ErrorPagesDeployer
{
    public function __construct(
        protected \Magento\Framework\App\State $state,
        protected \Magento\Framework\View\DesignInterface $design,
        protected \Magento\Theme\Model\Theme\ThemeProvider $themeProvider,
        protected \Magento\Framework\View\Design\Theme\Customization\Path $customization,
        protected \Magento\Framework\Filesystem\Driver\File $fileDriver,
        protected \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory,
        protected \Magento\Framework\Config\ScopeInterface $scope,
        protected \Magento\Framework\Filesystem\Io\File $file,
        protected \Magento\Framework\View\Design\Theme\ListInterface $themeList
    ) {
    }

    public function execute(): void
    {
        if ($this->scope->getCurrentScope() === 'primary') {
            $this->state->setAreaCode('frontend');
        }

        foreach ($this->themeList->getItems() as $key => $theme) {
            if (str_contains($key, \Magento\Framework\App\Area::AREA_ADMINHTML)) {
                continue;
            }

            $this->deployErrorPagesFromTheme($theme);
        }
    }

    protected function deployErrorPagesFromTheme($theme): void
    {
        $errorPath = $this->returnPathFromTheme($theme);
        if (!$errorPath) {
            return;
        }

        $this->copyErrorPages($errorPath);
    }

    protected function returnPathFromTheme($theme): ?string
    {
        $basePath = $this->customization->getThemeFilesPath($theme);
        $errorPath = $basePath . '/errors/';

        if ($this->file->fileExists($errorPath, false)) {
            return $errorPath;
        }

        return null;
    }

    protected function copyErrorPages($errorPages): void
    {
        $localXmlFile = $errorPages . 'local.xml';
        if ($this->file->fileExists($localXmlFile, false)) {
            $this->fileDriver->copy($errorPages . 'local.xml', BP . '/pub/errors/local.xml');
        }

        $this->copyRecursive($errorPages . '/', BP . '/pub/errors/');
    }

    protected function copyRecursive($source, $target): void
    {
        if (!$this->file->fileExists($target, false)) {
            $this->fileDriver->createDirectory($target);
        }

        $write = $this->writeFactory->create($source);
        $files = $write->readRecursively();

        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            if ($this->fileDriver->isDirectory($source . $file)) {
                $this->fileDriver->createDirectory($target . $file);
            } else {
                $this->fileDriver->copy($source . $file, $target . $file);
            }
        }
    }
}
