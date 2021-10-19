<?php

namespace Omnipay\AfterPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractRequest extends BaseAbstractRequest
{
    protected $liveEndpoint = 'https://api.afterpay.com/v2';
    protected $testEndpoint = 'https://api-sandbox.afterpay.com/v2';

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * @return mixed
     */
    public function getMerchantSecret()
    {
        return $this->getParameter('merchantSecret');
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setMerchantSecret($value)
    {
        return $this->setParameter('merchantSecret', $value);
    }

    /**
     * @param mixed $data
     * @return \Omnipay\AfterPay\Message\Response
     * @throws \Guzzle\Http\Exception\RequestException
     */
    // public function sendData($data)
    // {
    //     $endpoint = $this->getEndpoint();
    //     $httpMethod = $this->getHttpMethod();

    //     $httpRequest = $this->httpClient->request($httpMethod, $endpoint);
    //     $httpRequest->getCurlOptions()->set(CURLOPT_SSLVERSION, 6); // CURL_SSLVERSION_TLSv1_2
    //     $httpRequest->addHeader('Authorization', $this->buildAuthorizationHeader());
    //     $httpRequest->addHeader('Content-type', 'application/json');
    //     $httpRequest->addHeader('Accept', 'application/json');
    //     $httpRequest->setBody(json_encode($data));

    //     $httpResponse = $httpRequest->send();

    //     $this->response = $this->createResponse(
    //         $this->parseResponseData($httpResponse)
    //     );

    //     return $this->response;
    // }

    /**
     * @inheritDoc
     */
    public function sendData($data)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->buildAuthorizationHeader(),
            'User-Agent' => $this->buildUserAgentHeader(),
            // TODO: Add idempotency support using the `requestId` field.
        ];

        $httpResponse = $this->httpClient->request($this->getHttpMethod(), $this->getEndpoint(), $headers, json_encode($data));
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
     * @return \Omnipay\AfterPay\Message\Response
     */
    protected function createResponse($data)
    {
        return new Response($this, $data);
    }

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
        return "omnipay-afterpay/0.1 (PHP/" . phpversion() . "; Merchant/{$this->getMerchantId()})";
    }
}
