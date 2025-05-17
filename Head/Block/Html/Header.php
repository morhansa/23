<?php
namespace PortoFix\Head\Block\Html;

class Header extends \Magento\Theme\Block\Html\Header
{
    /**
     * @var \Smartwave\Porto\Helper\Data
     */
    protected $portoHelper;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Smartwave\Porto\Helper\Data $portoHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Smartwave\Porto\Helper\Data $portoHelper,
        array $data = []
    ) {
        $this->portoHelper = $portoHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    
    /**
     * Get Porto theme configuration
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->portoHelper->getConfig($path);
    }
    
    /**
     * Get header type from Porto configuration
     *
     * @return string
     */
    public function getHeaderType()
    {
        return $this->getConfig('porto_settings/header/header_type');
    }
    
    /**
     * Check if current page is homepage
     *
     * @return bool
     */
    public function isHomePage()
    {
        return $this->portoHelper->isHomePage();
    }
}