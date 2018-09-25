<?php

namespace MageSuite\MaintenancePage\Service;

class ErrorPagesDeployer
{
    const DEFAULT_CS_THEME_PATH = 'frontend/Creativestyle/theme-creativeshop';
    const DEFAULT_MAGENTO_THEME_PATH = 'frontend/Magento/luma';

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeProvider
     */
    private $themeProvider;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     */
    private $customization;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $write;

    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    private $scope;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Theme\Model\Theme\ThemeProvider $themeProvider,
        \Magento\Framework\View\Design\Theme\Customization\Path $customization,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\Directory\WriteFactory $write,
        \Magento\Framework\Config\ScopeInterface $scope
    )
    {
        $this->state = $state;
        $this->design = $design;
        $this->themeProvider = $themeProvider;
        $this->customization = $customization;
        $this->fileDriver = $fileDriver;
        $this->write = $write;
        $this->scope = $scope;
    }

    public function execute()
    {
        if ($this->scope->getCurrentScope() === 'primary') {
            $this->state->setAreaCode('frontend');
        }

        $themeId = $this->design->getConfigurationDesignTheme('frontend');

        if (!$themeId) {
            return false;
        }

        $themeId = !empty($themeId) ? $themeId : self::DEFAULT_MAGENTO_THEME_PATH;

        if (is_numeric($themeId)) {
            $theme = $this->themeProvider->getThemeById($themeId);
        } else {
            $theme = $this->themeProvider->getThemeByFullPath('frontend/' . $themeId);
        }

        if (!$theme) {
            return false;
        }

        $this->deployErrorPages($theme);
    }

    private function deployErrorPages($theme)
    {
        $defaultTheme = $this->themeProvider->getThemeByFullPath(self::DEFAULT_CS_THEME_PATH);

        if ($defaultTheme and $defaultTheme->getId()) {
            $this->deployErrorPagesFromTheme($defaultTheme);
        }

        $this->deployErrorPagesFromTheme($theme);

        return true;
    }

    private function deployErrorPagesFromTheme($theme)
    {
        $errorPath = $this->returnPathFromTheme($theme);

        if ($errorPath) {
            $this->copyErrorPages($errorPath);
        }
    }

    private function returnPathFromTheme($theme)
    {
        $basePath = $this->customization->getThemeFilesPath($theme);
        $errorPath = $basePath . '/errors/';

        if (file_exists($errorPath)) {
            return $errorPath;
        }

        return false;
    }

    private function copyErrorPages($errorPages)
    {
        $localXmlFile = $errorPages . 'local.xml';

        if (file_exists($localXmlFile)) {
            $this->fileDriver->copy($errorPages . 'local.xml', BP . '/pub/errors/local.xml');
        }

        $this->copyRecursive($errorPages . '/', BP . '/pub/errors/');
    }

    private function copyRecursive($source, $target)
    {
        if (!file_exists($target)) {
            $this->fileDriver->createDirectory($target);
        }

        $write = $this->write->create($source);
        $files = $write->readRecursively();

        if (empty($files)) {
            return false;
        }

        foreach ($files as $file) {

            if (is_dir($source . $file)) {
                $this->fileDriver->createDirectory($target . $file);
            } else {
                $this->fileDriver->copy($source . $file, $target . $file);
            }
        }

        return true;
    }
}