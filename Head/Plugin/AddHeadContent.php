<?php
namespace PortoFix\Head\Plugin;

class AddHeadContent
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->layout = $layout;
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }
    
    /**
     * @param \Magento\Framework\View\Page\Config\Renderer $subject
     * @param string $result
     * @return string
     */
    public function afterRenderHeadContent(\Magento\Framework\View\Page\Config\Renderer $subject, $result)
    {
        // محاولة استخدام بلوك محتوى الرأس مباشرة
        try {
            $headBlock = $this->layout->createBlock('Smartwave\Porto\Block\Template')
                ->setTemplate('Smartwave_Porto::html/head.phtml')
                ->toHtml();
                
            return $result . $headBlock;
        } catch (\Exception $e) {
            // استخدام مسارات مباشرة في حالة فشل البلوك
            $storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $store = $storeManager->getStore();
            $staticUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
            $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            
            $cssLinks = '';
            $cssLinks .= '<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans%3A300%2C300italic%2C400%2C400italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic&amp;v1&amp;subset=latin%2Clatin-ext" type="text/css" media="screen"/>';
            $cssLinks .= '<link href="//fonts.googleapis.com/css?family=Oswald:300,400,500,600,700" rel="stylesheet">';
            $cssLinks .= '<link href="//fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800" rel="stylesheet">';
            
            // مسارات ملفات CSS الأساسية
            $cssLinks .= '<link rel="stylesheet" type="text/css" media="all" href="' . $mediaUrl . 'porto/web/bootstrap/css/bootstrap.min.css">';
            $cssLinks .= '<link rel="stylesheet" type="text/css" media="all" href="' . $mediaUrl . 'porto/web/font-awesome/css/font-awesome.min.css">';
            $cssLinks .= '<link rel="stylesheet" type="text/css" media="all" href="' . $mediaUrl . 'porto/web/simple-line-icons/css/simple-line-icons.css">';
            $cssLinks .= '<link rel="stylesheet" type="text/css" media="all" href="' . $mediaUrl . 'porto/web/css/animate.css">';
            
            // نوع الهيدر
            $headerType = $this->getConfig('porto_settings/header/header_type');
            if (!$headerType) {
                $headerType = '1';
            }
            
            $cssLinks .= '<link rel="stylesheet" type="text/css" media="all" href="' . $mediaUrl . 'porto/web/css/header/type' . $headerType . '.css">';
            $cssLinks .= '<link rel="stylesheet" type="text/css" media="all" href="' . $mediaUrl . 'porto/web/css/porto.css">';
            $cssLinks .= '<link rel="stylesheet" type="text/css" media="all" href="' . $mediaUrl . 'porto/web/css/custom.css">';
            
            // JavaScript أساسي
            $jsContent = '<script type="text/javascript">';
            $jsContent .= 'window.theme = {};';
            $jsContent .= 'var js_porto_vars = {"rtl":""};';
            $jsContent .= 'var redirect_cart = ' . ($this->getConfig('checkout/cart/redirect_to_cart') ? 'true' : 'false') . ';';
            $jsContent .= '</script>';
            
            return $result . $cssLinks . $jsContent;
        }
    }
    
    /**
     * @param string $path
     * @return mixed
     */
    protected function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}