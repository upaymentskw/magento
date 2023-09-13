<?php
/**
 * InstallData
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Setup;


use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    const UPAYMENTS_TOKEN_ATTRIBUTE = "upayments_token";
    private $customerSetupFactory;

    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(\Magento\Customer\Model\Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            self::UPAYMENTS_TOKEN_ATTRIBUTE,
            [
                'type' => 'text',
                'label' => 'Upayments Token',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'user_defined' => true,
                'sort_order' => 1000,
                'position' => 1000,
                'system' => 0,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            self::UPAYMENTS_TOKEN_ATTRIBUTE
        );

        $attribute->addData(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $customerEntity->getDefaultAttributeGroupId(),
                'used_in_forms' => ['adminhtml_customer'],
            ]
        );

        $attribute->save();
    }
}
