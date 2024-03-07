<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at http://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_CustomerApproval
 * @copyright Copyright (C) 2021 Magezon (http://magezon.com)
 */

namespace Magezon\CustomerApproval\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ListStatus extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const STATUS_REJECTED = 0;
    const STATUS_APPROVED = 1;
    const STATUS_PENDING = 2;

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = [
            [
                'label' => __('Rejected'),
                'value' => self::STATUS_REJECTED,
            ],
            [
                'label' => __('Approved'),
                'value' => self::STATUS_APPROVED,
            ],
            [
                'label' => __('Pending'),
                'value' => self::STATUS_PENDING,
            ]
        ];
        return $options;
    }
}
