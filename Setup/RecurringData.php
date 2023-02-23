<?php

namespace MageSuite\MaintenancePage\Setup;

class RecurringData implements \Magento\Framework\Setup\InstallDataInterface
{
    protected \MageSuite\MaintenancePage\Service\ErrorPagesDeployer $errorPagesDeployer;

    public function __construct(
        \MageSuite\MaintenancePage\Service\ErrorPagesDeployer $errorPagesDeployer
    ) {
        $this->errorPagesDeployer = $errorPagesDeployer;
    }

    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ): void {
        $this->errorPagesDeployer->execute();
    }
}
