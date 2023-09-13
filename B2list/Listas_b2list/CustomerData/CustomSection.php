<?php
namespace B2list\Listas_b2list\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\Cache\Type\Block as BlockCache;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context;


class CustomSection implements SectionSourceInterface
{
    protected $customerSession;
    protected $httpContext;
    protected $blockCache;

    public function __construct(
        CustomerSession $customerSession,
        Context $httpContext,
        BlockCache $blockCache
    ) {
        $this->customerSession  = $customerSession;
        $this->httpContext      = $httpContext;
        $this->blockCache       = $blockCache;
    }

    public function getSectionData()
    {
        $this->blockCache->clean();

        // Agora você pode acessar a sessão do cliente e o contexto aqui
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $customerId = $this->customerSession->getCustomerId();

        // Lógica para recuperar os dados da seção aqui
        $data = [
            'is_logged_in' => $isLoggedIn,
            'customer_id' => $customerId,
        ];

        return $data;
    }
}