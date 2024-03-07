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

namespace Magezon\CustomerApproval\Plugin\Framework\View\Element;

use Magezon\CustomerApproval\Helper\Data;

class UiComponentFactory {

	public function beforeCreate ($subject, $identifier, $name = null, array $arguments = []) {
		if ($identifier == Data::IS_APPROVED) {
			$name = Data::SELECT_TYPE;
		}
		return [$identifier, $name, $arguments];
	}
}