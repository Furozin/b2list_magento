<?php
namespace B2list\Listas_b2list\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use B2list\Listas_b2list\CustomerData\CustomSection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;

class MenuPlugin
{
    protected $customerSession;
    protected $curl;
    protected $customerRepository;
    protected $customSection;
    protected $urlBuilder;
    protected $nodeFactory;
    
    public function __construct(
        CustomerSession $customerSession, 
        Curl $curl, 
        CustomerRepositoryInterface $customerRepository, 
        CustomSection $customSection,
        UrlInterface $urlBuilder,
        NodeFactory $nodeFactory
    )
    {
        $this->customerSession = $customerSession;
        $this->curl = $curl;
        $this->customerRepository = $customerRepository;
        $this->customSection = $customSection;
        $this->urlBuilder = $urlBuilder;
        $this->nodeFactory = $nodeFactory;
    }

    public function beforeGetHtml(\Magento\Theme\Block\Html\Topmenu $subject, $outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {


        if (!empty($this->customerSession->isLoggedIn())) {
        // Obtenha os dados da seção personalizada
        $customSectionData = $this->customSection->getSectionData();

        $data_agora = date('Y/m/d H:i:s');

        //Bloco para pegar CNPJ do cliente logado
        $customerId = $this->customerSession->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        // Obter o taxvat do cliente
        $taxvat = $customer->getTaxvat();
        // var_dump($taxvat);
        $cnpj = '48963321000118'; // Seu CNPJ de filtro

        // URL da API
        $url = 'https://hml-services.b2list.com/vilanova2/list-items/query';

        // Configurar os cabeçalhos
        $headers = [
            "X-TOKEN" => "pVMEvSEpfmFmydlkof3PuE0tHmHG3K3o",
            "Content-Type" => "application/json"
        ];

        // Configurar o corpo da requisição
        // $body = [
        //     "direction" => "ASC",
        //     "page" => 0,
        //     "size" => 10,
        //     "sort" => false,
        //     "sortColumn" => "string"                
        // ];

        $body = [
            "conditions" => [
                [
                    "key" => "expiresIn",
                    "operator" => "AFTER",
                    "value" => $data_agora
                ],
                [
                    "key" => "buyerCnpj",
                    "operator" => "EQ",
                    "value" => $cnpj
                ]
            ],
            "direction" => "DESC",
            "page" => 0,
            "size" => 10,
            "sort" => false,
            "sortColumn" => "date"
        ];

        // var_dump($body);die;

    
        // Configurar o cliente HTTP e fazer a requisição
        $this->curl->setHeaders($headers);
        $this->curl->post($url, json_encode($body));

        // Obter a resposta
        $response = $this->curl->getBody(); 

        $clientes = json_decode($response, true);


        if( !empty($clientes))
            $id_link = $clientes['results'][0]['id'];
        

              
        //verificar comportamento no ambiente de produção e na firestore, porque só esta funcionando se rodar o comando no terminal: sudo bin/magento cache:flush
       
            // Obter o ID do cliente logado
                        
            $node = new \Magento\Framework\Data\Tree\Node(
                [
                    'name' => __('Listas - B2list'),
                    'id' => 'custom-link',
                    'url' => $this->urlBuilder->getUrl('https://hml-vilanova2.b2list.com/order/'  . $id_link ),
                    'target' => '_blank', // Isso define o link para abrir em uma nova guia/janela
                ],
                'id',
                $subject->getMenu()->getTree()
            );

            $subject->getMenu()->addChild($node);
        }
    }
}