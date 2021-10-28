<?php

namespace Omnipay\AfterPay\Message;

use Omnipay\Common\Message\AbstractResponse;

class GetCheckoutRequest extends AbstractRequest
{
    /**
     * @inheritDoc
     */
    public function getData()
    {
        return [
            // NOTE: Still including `$this->httpRequest->get('orderToken')` for potential backwards compatibility.
            'token'             => $this->getTransactionReference() ?: $this->httpRequest->get('orderToken'),
            'merchantReference' => $this->getTransactionId(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        $data = $this->getData();
        return $data['token'] ?? null;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . "/checkouts/{$this->getToken()}";
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return 'GET';
    }

    /**
     * @inheritDoc
     */
    protected function createResponse($data): AbstractResponse
    {
        return new GetCheckoutResponse($this, $data);
    }
}
