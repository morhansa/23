<?php
namespace PortoFix\Head\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\Page;
use Smartwave\Porto\Block\Template;

class HeaderRenderer
{
    /**
     * @var RequestInterface
     */
    protected $request;
    
    /**
     * @var LayoutInterface
     */
    protected $layout;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;
    
    /**
     * @var \Smartwave\Porto\Helper\Data
     */
    protected $portoHelper;
    
    /**
     * @param RequestInterface $request
     * @param LayoutInterface $layout
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Smartwave\Porto\Helper\Data $portoHelper
     */
    public function __construct(
        RequestInterface $request,
        LayoutInterface $layout,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Smartwave\Porto\Helper\Data $portoHelper
    ) {
        $this->request = $request;
        $this->layout = $layout;
        $this->scopeConfig = $scopeConfig;
        $this->blockFactory = $blockFactory;
        $this->portoHelper = $portoHelper;
    }
    
    /**
     * Inject header content into DOM
     *
     * @param Page $subject
     * @param Page $result
     * @return Page
     */
    public function afterRenderResult(Page $subject, $result)
    {
        try {
            // Get layout from the page
            $layout = $subject->getLayout();
            
            // Check if Porto header block exists
            if (!$layout->getBlock('porto_header')) {
                // Create header block
                $headerBlock = $this->blockFactory->createBlock(
                    Template::class,
                    'porto_header',
                    ['data' => [
                        'template' => 'Smartwave_Porto::html/header.phtml',
                        'area' => 'frontend'
                    ]]
                );
                
                // Add child blocks to header
                $this->addHeaderChildBlocks($headerBlock, $layout);
                
                // Add header to page wrapper
                $pageWrapperContainer = $layout->getBlock('page.wrapper');
                if ($pageWrapperContainer) {
                    $pageWrapperContainer->setChild('porto_header', $headerBlock);
                    
                    // Move page.top after porto_header
                    if ($layout->getBlock('page.top')) {
                        $layout->reorderChild('page.wrapper', 'page.top', 'porto_header');
                    }
                }
                
                // Also add footer
                $footerBlock = $this->blockFactory->createBlock(
                    Template::class,
                    'footer_block',
                    ['data' => [
                        'template' => 'Smartwave_Porto::html/footer.phtml',
                        'area' => 'frontend'
                    ]]
                );
                
                $footerContainer = $layout->getBlock('footer-container');
                if ($footerContainer) {
                    $footerContainer->setChild('footer_block', $footerBlock);
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            // Log error if needed
            return $result;
        }
    }
    
    /**
     * Add child blocks to the header
     * 
     * @param \Magento\Framework\View\Element\BlockInterface $headerBlock
     * @param LayoutInterface $layout
     * @return void
     */
    protected function addHeaderChildBlocks($headerBlock, $layout)
    {
        // Create and add necessary child blocks
        $headerLinks = $layout->createBlock(
            'Magento\Framework\View\Element\Html\Links',
            'header.links',
            ['data' => ['css_class' => 'header links']]
        );
        $headerBlock->setChild('header.links', $headerLinks);
        
        // Add login form popup if enabled in config
        if ($this->portoHelper->getConfig('porto_settings/header/login_popup')) {
            $loginForm = $layout->createBlock(
                'Smartwave\Porto\Block\Form\Login',
                'header_customer_form_login',
                ['data' => ['template' => 'Smartwave_Porto::html/login.phtml']]
            );
            $headerBlock->setChild('header_customer_form_login', $loginForm);
        }
        
        // Add language switcher
        $langSwitcher = $layout->createBlock(
            'Magento\Store\Block\Switcher',
            'store_language',
            ['data' => ['template' => 'switch/languages.phtml']]
        );
        $headerBlock->setChild('store_language', $langSwitcher);
        
        // Add search form
        $searchForm = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'top.search',
            ['data' => ['template' => 'Magento_Search::form.mini.phtml']]
        );
        $headerBlock->setChild('top.search', $searchForm);
        
        // Add custom blocks if enabled in config
        if ($this->portoHelper->getConfig('porto_settings/header/static_block')) {
            $customBlock = $layout->createBlock(
                'Smartwave\Porto\Block\Template',
                'custom_block',
                ['data' => ['template' => 'html/header_custom_block.phtml']]
            );
            $headerBlock->setChild('custom_block', $customBlock);
        }
        
        if ($this->portoHelper->getConfig('porto_settings/header/static_block_top')) {
            $customBlockTop = $layout->createBlock(
                'Smartwave\Porto\Block\Template',
                'custom_block_top',
                ['data' => ['template' => 'html/top_custom_block.phtml']]
            );
            $headerBlock->setChild('custom_block_top', $customBlockTop);
        }
        
        // Add additional blocks
        $customBlockMenu = $layout->createBlock(
            'Smartwave\Porto\Block\Template',
            'custom_block_menu',
            ['data' => ['template' => 'html/header_custom_block_menu.phtml']]
        );
        $headerBlock->setChild('custom_block_menu', $customBlockMenu);
        
        $customNotice = $layout->createBlock(
            'Smartwave\Porto\Block\Template',
            'porto_custom_notice',
            ['data' => ['template' => 'html/custom_notice.phtml']]
        );
        $headerBlock->setChild('porto_custom_notice', $customNotice);
    }
}