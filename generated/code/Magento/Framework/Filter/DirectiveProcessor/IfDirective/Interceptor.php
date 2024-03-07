<?php
namespace Magento\Framework\Filter\DirectiveProcessor\IfDirective;

/**
 * Interceptor class for @see \Magento\Framework\Filter\DirectiveProcessor\IfDirective
 */
class Interceptor extends \Magento\Framework\Filter\DirectiveProcessor\IfDirective implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Filter\VariableResolverInterface $variableResolver)
    {
        $this->___init();
        parent::__construct($variableResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $construction, \Magento\Framework\Filter\Template $filter, array $templateVariables) : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'process');
        return $pluginInfo ? $this->___callPlugins('process', func_get_args(), $pluginInfo) : parent::process($construction, $filter, $templateVariables);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularExpression() : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getRegularExpression');
        return $pluginInfo ? $this->___callPlugins('getRegularExpression', func_get_args(), $pluginInfo) : parent::getRegularExpression();
    }
}
