<?php

namespace MageSuite\MaintenancePage\Service;

class ErrorPagesDeployer
{
    protected const CREATIVESHOP_THEME_PATH = 'frontend/Creativestyle/theme-creativeshop';
    protected const LUMA_THEME_PATH = 'frontend/Magento/luma';

    protected \Magento\Framework\App\State $state;
    protected \Magento\Framework\View\DesignInterface $design;
    protected \Magento\Theme\Model\Theme\ThemeProvider $themeProvider;
    protected \Magento\Framework\View\Design\Theme\Customization\Path $customization;
    protected \Magento\Framework\Filesystem\Driver\File $fileDriver;
    protected \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory;
    protected \Magento\Framework\Config\ScopeInterface $scope;
    protected \Magento\Framework\Filesystem\Io\File $file;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Theme\Model\Theme\ThemeProvider $themeProvider,
        \Magento\Framework\View\Design\Theme\Customization\Path $customization,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory,
        \Magento\Framework\Config\ScopeInterface $scope,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->state = $state;
        $this->design = $design;
        $this->themeProvider = $themeProvider;
        $this->customization = $customization;
        $this->fileDriver = $fileDriver;
        $this->writeFactory = $writeFactory;
        $this->scope = $scope;
        $this->file = $file;
    }

    public function execute(): void
    {
        if ($this->scope->getCurrentScope() === 'primary') {
            $this->state->setAreaCode('frontend');
        }

        $themeId = $this->design->getConfigurationDesignTheme('frontend');

        if (!$themeId) {
            return;
        }

        $themeId = !empty($themeId)
            ? $themeId
            : self::LUMA_THEME_PATH;

        $theme = is_numeric($themeId)
            ? $this->themeProvider->getThemeById($themeId)
            : $this->themeProvider->getThemeByFullPath('frontend/' . $themeId);

        if (!$theme) {
            return;
        }

        $this->deployErrorPages($theme);
    }

    protected function deployErrorPages($currentTheme): void
    {
        $creativeshopTheme = $this->themeProvider->getThemeByFullPath(self::CREATIVESHOP_THEME_PATH);

        if ($creativeshopTheme && $currentTheme->getCode() != 'Magento/luma') {
            $this->deployErrorPagesFromTheme($creativeshopTheme);
        }

        $this->deployErrorPagesFromTheme($currentTheme);
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
