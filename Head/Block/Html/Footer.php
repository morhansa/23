<?php
namespace PortoFix\Head\Block\Html;

class Footer extends \Magento\Theme\Block\Html\Footer
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
     * Get footer logo URL
     *
     * @return string|null
     */
    public function getFooterLogoSrc()
    {
        $folderName = \Smartwave\Porto\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->getConfig('porto_settings/footer/footer_logo_src');
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->portoHelper->getBaseUrl() . $path;
        
        return $storeLogoPath ? $logoUrl : null;
    }
}