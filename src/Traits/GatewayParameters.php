<?php

namespace Omnipay\AfterPay\Traits;

trait GatewayParameters
{
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
     */
    public function setMerchantSecret($value)
    {
        return $this->setParameter('merchantSecret', $value);
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        return $this->getParameter('userAgent');
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setUserAgent($value)
    {
        return $this->setParameter('userAgent', $value);
    }
}
