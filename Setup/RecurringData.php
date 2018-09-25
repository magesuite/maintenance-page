<?php

namespace MageSuite\MaintenancePage\Setup;

class RecurringData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * @param \MageSuite\MaintenancePage\Service\ErrorPagesDeployer
     */
    private $errorPagesDeployer;

    public function __construct(
        \MageSuite\MaintenancePage\Service\ErrorPagesDeployer $errorPagesDeployer
    ) {
        $this->errorPagesDeployer = $errorPagesDeployer;
    }

    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $this->errorPagesDeployer->execute();
    }
}
