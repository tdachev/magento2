<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Newsletter\Controller\Manage;

class Save extends \Magento\Newsletter\Controller\Manage
{
    /**
     * Save newsletter subscription preference action
     *
     * @return void|null
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('customer/account/');
        }

        $customerId = $this->_customerSession->getCustomerId();
        if (is_null($customerId)) {
            $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
        } else {
            try {
                $customer = $this->_customerAccountService->getCustomer($customerId);
                $storeId = $this->_storeManager->getStore()->getId();
                $customerDetails = $this->_customerDetailsBuilder->setAddresses(null)
                    ->setCustomer($this->_customerBuilder->populate($customer)->setStoreId($storeId)->create())
                    ->create();
                $this->_customerAccountService->updateCustomer($customerId, $customerDetails);

                if ((boolean)$this->getRequest()->getParam('is_subscribed', false)) {
                    $this->_subscriberFactory->create()->subscribeCustomerById($customerId);
                    $this->messageManager->addSuccess(__('We saved the subscription.'));
                } else {
                    $this->_subscriberFactory->create()->unsubscribeCustomerById($customerId);
                    $this->messageManager->addSuccess(__('We removed the subscription.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
            }
        }
        $this->_redirect('customer/account/');
    }
}
