<?php

namespace Selesti\Calor\Plugin;

use Magento\LoginAsCustomerAssistance\Model\ResourceModel\GetLoginAsCustomerAssistanceAllowed;

class GetLoginAsCustomerAssistanceAllowedPlugin
{

    /**
     * @param GetLoginAsCustomerAssistanceAllowed $subject
     * @param bool $result
     * @return bool
     */
    public function afterExecute(
        GetLoginAsCustomerAssistanceAllowed $subject,
        bool                                $result
    )
    {
        return true;
    }

}
