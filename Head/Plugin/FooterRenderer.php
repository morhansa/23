<?php
namespace PortoFix\Head\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\Page;
use Smartwave\Porto\Block\Template;

class FooterRenderer
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
     * Add footer block to result page
     *
     * @param Page $subject
     * @param Page $result
     * @return Page
     */
    public function afterRenderResult(Page $subject, $result)
    {
        try {
            // Get layout from the result page
            $layout = $subject->getLayout();
            
            // Check if footer_block exists
            if (!$layout->getBlock('footer_block')) {
                // Create footer block
                $footerBlock = $this->blockFactory->createBlock(
                    Template::class,
                    'footer_block',
                    ['data' => [
                        'template' => 'Smartwave_Porto::html/footer.phtml',
                        'area' => 'frontend'
                    ]]
                );
                
                // Add child blocks to footer
                $this->addFooterChildBlocks($footerBlock, $layout);
                
                // Add footer to container
                $footerContainer = $layout->getBlock('footer-container');
                if ($footerContainer) {
                    $footerContainer->setChild('footer_block', $footerBlock);
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            // In case of error, return original result
            return $result;
        }
    }
    
    /**
     * Add child blocks to the footer
     * 
     * @param \Magento\Framework\View\Element\BlockInterface $footerBlock
     * @param LayoutInterface $layout
     * @return void
     */
    protected function addFooterChildBlocks($footerBlock, $layout)
    {
        // Add store switcher
        $storeSwitcher = $layout->createBlock(
            'Magento\Store\Block\Switcher',
            'footer.store_switcher',
            ['data' => ['template' => 'switch/stores.phtml']]
        );
        $footerBlock->setChild('footer.store_switcher', $storeSwitcher);
        
        // Add newsletter subscription
        $newsletter = $layout->createBlock(
            'Magento\Newsletter\Block\Subscribe',
            'footer.newsletter',
            ['data' => ['template' => 'subscribe_footer.phtml']]
        );
        $footerBlock->setChild('footer.newsletter', $newsletter);
        
        // Add custom blocks based on Porto theme configuration
        $footerConfig = $this->portoHelper->getConfig('porto_settings/footer');
        
        if (isset($footerConfig['footer_top_custom']) && $footerConfig['footer_top_custom']) {
            $topCustomBlock = $layout->createBlock(
                'Magento\Cms\Block\Block',
                'footer_top_custom',
                ['data' => ['block_id' => $footerConfig['footer_top_custom']]]
            );
            $footerBlock->setChild('footer_top_custom', $topCustomBlock);
        }
        
        // Add additional footer sections from configuration
        for ($i = 1; $i <= 4; $i++) {
            $colName = 'footer_middle_column_' . $i;
            if (isset($footerConfig[$colName . '_custom']) && $footerConfig[$colName . '_custom']) {
                $customBlock = $layout->createBlock(
                    'Magento\Cms\Block\Block',
                    'footer_middle_col_' . $i,
                    ['data' => ['block_id' => $footerConfig[$colName . '_custom']]]
                );
                $footerBlock->setChild('footer_middle_col_' . $i, $customBlock);
            }
        }
        
        // Add bottom custom blocks
        if (isset($footerConfig['footer_bottom_custom_1']) && $footerConfig['footer_bottom_custom_1']) {
            $bottomBlock1 = $layout->createBlock(
                'Magento\Cms\Block\Block',
                'footer_bottom_custom_1',
                ['data' => ['block_id' => $footerConfig['footer_bottom_custom_1']]]
            );
            $footerBlock->setChild('footer_bottom_custom_1', $bottomBlock1);
        }
        
        if (isset($footerConfig['footer_bottom_custom_2']) && $footerConfig['footer_bottom_custom_2']) {
            $bottomBlock2 = $layout->createBlock(
                'Magento\Cms\Block\Block',
                'footer_bottom_custom_2',
                ['data' => ['block_id' => $footerConfig['footer_bottom_custom_2']]]
            );
            $footerBlock->setChild('footer_bottom_custom_2', $bottomBlock2);
        }
    }
}