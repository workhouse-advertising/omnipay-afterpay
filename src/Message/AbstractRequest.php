<?php

namespace Omnipay\AfterPay\Message;

use Omnipay\AfterPay\Traits\GatewayParameters;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractRequest extends BaseAbstractRequest
{
    use GatewayParameters;

    protected $liveEndpoint = 'https://api.afterpay.com/v2';
    protected $testEndpoint = 'https://api-sandbox.afterpay.com/v2';

    /**
     * @inheritDoc
     */
    protected $negativeAmountAllowed = true;

    /**
     * @inheritDoc
     */
    public function sendData($data)
    {
        // TODO: Add idempotency support using the `requestId` field.

        $httpResponse = $this->httpClient->request($this->getHttpMethod(), $this->getEndpoint(), $this->getHeaders(), json_encode($data));
        $responseData = json_decode($httpResponse->getBody(), true);

        // NOTE: Any 2xx response is to be considered to be successful, although the documentation at `https://developers.afterpay.com/afterpay-online/reference/create-checkout-1`
        //       indicates that a 200 is expected, we appear to receive a 201 on success.
        if ($httpResponse->getStatusCode() < 200 || $httpResponse->getStatusCode() > 299) {
            // TODO: Consider filtering the response body in case it may have sensitive information in there.
            //       Although that _should_ never occur.
            // TODO: Consider adding support for accessing the errors in the body. Perhaps return an AuthorizeResponse with errors?
            //       Or maybe add a "debug" mode to this package?
            throw new InvalidRequestException("Invalid request to the AfterPay API. Received status code '{$httpResponse->getStatusCode()}'.");
        }

        return $this->createResponse(
            $this->parseResponseData($httpResponse)
        );
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->buildAuthorizationHeader(),
            'User-Agent' => $this->buildUserAgentHeader(),
        ];
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return 'POST';
    }

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $httpResponse
     * @return array
     */
    protected function parseResponseData(ResponseInterface $httpResponse)
    {
        return json_decode($httpResponse->getBody(), true);
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\AbstractResponse
     */
    abstract protected function createResponse($data): AbstractResponse;

    /**
     * @return string
     */
    protected function buildAuthorizationHeader()
    {
        $merchantId = $this->getMerchantId();
        $merchantSecret = $this->getMerchantSecret();

        return 'Basic ' . base64_encode($merchantId . ':' . $merchantSecret);
    }

    /**
     * @return string
     */
    protected function buildUserAgentHeader()
    {
        // Format of "{pluginOrModuleOrClientLibrary}/{pluginVersion} ({platform}/{platformVersion}; Merchant/{merchantId})"
        return $this->getUserAgent() ?: "omnipay-afterpay/0.1 (PHP/" . phpversion() . "; Merchant/{$this->getMerchantId()})";
    }
}
