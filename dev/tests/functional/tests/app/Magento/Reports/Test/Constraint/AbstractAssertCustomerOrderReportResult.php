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

namespace Magento\Reports\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\CustomerInjectable;

/**
 * Class AbstractAssertCustomerOrderReportResult
 * Check Order report grid for all params
 */
abstract class AbstractAssertCustomerOrderReportResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Prepare filter
     *
     * @param CustomerInjectable $customer
     * @param array $columns
     * @param array $report
     * @return array
     */
    public function prepareFilter(CustomerInjectable $customer, array $columns, array $report)
    {
        $format = '';
        switch ($report['report_period']) {
            case 'Day':
                $format = 'M j, Y';
                break;
            case 'Month':
                $format = 'j/Y';
                break;
            case 'Year':
                $format = 'Y';
                break;
        }

        return [
            'date' => date($format),
            'customer' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            'orders' => $columns['orders'],
            'average' => number_format($columns['average'], 2),
            'total' => number_format($columns['total'], 2)
        ];
    }
}
