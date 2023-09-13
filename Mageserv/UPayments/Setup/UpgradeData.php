<?php
/**
 * UpgradeData
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    const UPAY_ORDER_ID = "upay_order_id";
    const UPAY_TRACK_ID = "upay_track_id";
    private $salesSetupFactory;

    public function __construct(
        SalesSetupFactory $salesSetupFactory
    ){
        $this->salesSetupFactory = $salesSetupFactory;
    }
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface  $context)
    {
        if (version_compare($context->getVersion(), "3.1.2", "<")) {
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                Order::ENTITY,
                self::UPAY_ORDER_ID,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
            $salesSetup->addAttribute(
                Order::ENTITY,
                self::UPAY_TRACK_ID,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
        }
    }
}
