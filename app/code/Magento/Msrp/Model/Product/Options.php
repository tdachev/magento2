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
namespace Magento\Msrp\Model\Product;

use Magento\Msrp\Model\Product\Attribute\Source\Type\Price as TypePrice;

class Options
{
    /**
     * @var \Magento\Msrp\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpData;

    /**
     * @param \Magento\Msrp\Model\Config $config
     * @param \Magento\Msrp\Helper\Data $msrpData
     */
    public function __construct(
        \Magento\Msrp\Model\Config $config,
        \Magento\Msrp\Helper\Data $msrpData
    ) {
        $this->config = $config;
        $this->msrpData = $msrpData;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param null $visibility
     * @return bool|null
     */
    public function isEnabled($product, $visibility = null)
    {
        $visibilities = $this->getVisibilities($product);

        $result = (bool)$visibilities ? true : null;
        if ($result && $visibility !== null) {
            if ($visibilities) {
                $maxVisibility = max($visibilities);
                $result = $result && $maxVisibility == $visibility;
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getVisibilities($product)
    {
        /** @var \Magento\Catalog\Model\Product[] $collection */
        $collection = $product->getTypeInstance()->getAssociatedProducts($product)?: [];
        $visibilities = [];
        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($collection as $item) {
            if ($this->msrpData->canApplyMsrp($item)) {
                $visibilities[] = $item->getMsrpDisplayActualPriceType() == TypePrice::TYPE_USE_CONFIG
                    ? $this->config->getDisplayActualPriceType()
                    : $item->getMsrpDisplayActualPriceType();
            }
        }
        return $visibilities;
    }
}
