<?php
namespace WeltPixel\AdvancedWishlist\Controller\View;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Collections
 * @package WeltPixel\AdvancedWishlisy\Controller\View
 */
class Editable extends Action
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Editable constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    /**
     * @return ResponseInterface|Forward|ResultInterface
     */
    public function execute()
    {
        $result = [
            'editable' => false
        ];

        /**
         * The comparison is strict and the login is checked first. A loose compare against a
         * session customer id of null matched an empty or absent profile_customer_id, so a guest
         * could be told the profile was editable.
         */
        $result['editable'] = $this->customerSession->isLoggedIn()
            && (int)$this->customerSession->getCustomerId()
                === (int)$this->getRequest()->getParam('profile_customer_id')
            && (int)$this->customerSession->getCustomerId() > 0;

        return $this->prepareResult($result);
    }

    /**
     * @param array $result
     * @return string
     */
    protected function prepareResult($result)
    {
        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
