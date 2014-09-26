<?php
/**
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
namespace Magento\UrlRewrite\Model;

/**
 * @method int getEntityId()
 * @method string getEntityType()
 * @method int getRedirectType()
 * @method int getStoreId()
 * @method int getIsAutogenerated()
 * @method string getTargetPath()
 * @method UrlRewrite setEntityId(int $value)
 * @method UrlRewrite setEntityType(string $value)
 * @method UrlRewrite setMetadata($value)
 * @method UrlRewrite setRequestPath($value)
 * @method UrlRewrite setTargetPath($value)
 * @method UrlRewrite setRedirectType($value)
 * @method UrlRewrite setStoreId($value)
 * @method UrlRewrite setDescription($value)
 */
class UrlRewrite extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize corresponding resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\UrlRewrite\Model\Resource\UrlRewrite');
        $this->_collectionName = 'Magento\UrlRewrite\Model\Resource\UrlRewriteCollection';
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        $metadata = $this->getData(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::METADATA);
        return !empty($metadata) ? unserialize($metadata) : [];
    }
}
