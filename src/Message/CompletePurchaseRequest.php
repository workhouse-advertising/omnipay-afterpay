<?php

namespace Omnipay\AfterPay\Message;

use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseRequest extends AbstractRequest
{
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        return [
            'token'             => $this->httpRequest->get('orderToken'),
            'merchantReference' => $this->getTransactionId(),
        ];
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/capture';
    }

    /**
     * @inheritDoc
     */
    protected function createResponse($data): AbstractResponse
    {
        return new CompletePurchaseResponse($this, $data);
    }
}
