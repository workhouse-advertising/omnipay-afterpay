<?php

namespace Omnipay\AfterPay;

use Omnipay\Common\AbstractGateway;
use Omnipay\AfterPay\Traits\GatewayParameters;

class Gateway extends AbstractGateway
{
    use GatewayParameters;

    /**
     * @return string
     */
    public function getName()
    {
        return 'AfterPay';
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'merchantId'     => '',
            'merchantSecret' => '',
            'userAgent'      => null,
            'testMode'       => false,
        );
    }

    /**
     * Configuration Request.
     *
     * Retrieves a list of payment configuration that includes payment types
     * and valid payment ranges. A request to create an Order will be rejected
     * if the total amount is not between (inclusive) the minimumAmount and
     * minimumAmount.
     *
     * @param array $options
     * @return \Omnipay\AfterPay\Message\ConfigurationRequest
     */
    public function configuration(array $options = array())
    {
        return $this->createRequest(\Omnipay\AfterPay\Message\ConfigurationRequest::class, $options);
    }

    /**
     * Authorize and immediately capture an amount on the customers card
     *
     * @param array $options
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    public function purchase(array $options = array())
    {
        return $this->createRequest(\Omnipay\AfterPay\Message\PurchaseRequest::class, $options);
    }

    /**
     * Handle return from off-site gateways after purchase
     *
     * @param array $options
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    public function completePurchase(array $options = array())
    {
        return $this->createRequest(\Omnipay\AfterPay\Message\CompletePurchaseRequest::class, $options);
    }
}
