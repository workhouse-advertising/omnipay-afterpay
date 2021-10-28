<?php

namespace Omnipay\AfterPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Exception\InvalidRequestException;

class PurchaseRequest extends AbstractRequest
{
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        /** @var \Omnipay\Common\CreditCard $card */
        $card = $this->getCard();

        // Normalize consumer names as AfterPay will reject the request with a missing surname
        $givenNames = $card->getFirstName();
        $surname = $card->getLastName();

        // TODO: Investigate if this is even necessary or if we should just say that first and last
        //       names are required.
        if (empty($surname) && false !== $pos = strrpos($givenNames, ' ')) {
            $surname = substr($givenNames, $pos + 1);
            $givenNames = substr($givenNames, 0, $pos);
        }

        // TODO: Test if this is still required with the current version of the API.
        // Append fix query param to urls with existing query params as AfterPay appends their
        // data in a way that can break the base url
        // $returnUrl = $this->getReturnUrl();
        // $cancelUrl = $this->getCancelUrl();

        // if (strpos($returnUrl, '?') !== false) {
        //     $returnUrl .= '&_fix=';
        // }

        // if (strpos($cancelUrl, '?') !== false) {
        //     $cancelUrl .= '&_fix=';
        // }

        $data = array(
            'amount'       => array(
                'amount'   => $this->getAmount(),
                'currency' => $this->getCurrency(),
            ),
            // TODO: Add support for `taxAmount` and `shippingAmount
            // 'taxAmount'       => array(
            //     'amount'   => $this->getAmount(),
            //     'currency' => $this->getCurrency(),
            // ),
            // 'shippingAmount'       => array(
            //     'amount'   => $this->getAmount(),
            //     'currency' => $this->getCurrency(),
            // ),
            'consumer'          => array(
                'givenNames'  => $givenNames,
                'surname'     => $surname,
                'email'       => $card->getEmail(),
                'phoneNumber' => $card->getPhone(),
            ),
            'billing'           => array(
                'name'        => $card->getBillingName(),
                'line1'       => $card->getBillingAddress1(),
                'line2'       => $card->getBillingAddress2(),
                'area1'       => $card->getBillingCity(),
                'region'      => $card->getBillingState(),
                'postcode'    => $card->getBillingPostcode(),
                'countryCode' => $card->getBillingCountry(),
                'phoneNumber' => $card->getBillingPhone(),
            ),
            'shipping'          => array(
                'name'        => $card->getShippingName(),
                'line1'       => $card->getShippingAddress1(),
                'line2'       => $card->getShippingAddress2(),
                'area1'       => $card->getShippingCity(),
                'region'      => $card->getShippingState(),
                'postcode'    => $card->getShippingPostcode(),
                'countryCode' => $card->getShippingCountry(),
                'phoneNumber' => $card->getShippingPhone(),
            ),
            'items'             => $this->getItemData(),
            'discounts'         => $this->getDiscountData(),
            'merchant'          => array(
                // Need to append dummy parameter otherwise AfterPay breaks the hash param on return
                // TODO: Investigate if AfterPay still breaks the hash param on return with the current API version.
                'redirectConfirmUrl' => $this->getReturnUrl(),
                'redirectCancelUrl'  => $this->getCancelUrl(),
            ),
            'merchantReference' => $this->getTransactionId(),
        );

        return $data;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getItemData()
    {
        $items = $this->getItems();
        $itemArray = array();

        if ($items !== null) {
            /** @var \Omnipay\Common\ItemInterface $item */
            foreach ($items as $item) {
                // Skip any discounts.
                if ($item->getPrice() < 0) continue;

                $itemArray[] = array(
                    'name'     => $item->getName(),
                    'quantity' => $item->getQuantity(),
                    'price'    => array(
                        'amount'   => $this->formatPrice($item->getPrice()),
                        'currency' => $this->getCurrency(),
                    ),
                );
            }
        }

        return $itemArray;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getDiscountData()
    {
        $items = $this->getItems();
        $discountsArray = [];

        if ($items !== null) {
            /** @var \Omnipay\Common\ItemInterface $item */
            foreach ($items as $item) {
                // Skip any non-discounts.
                if ($item->getPrice() >= 0) continue;

                $discountsArray[] = [
                    'displayName'     => $item->getName(),
                    'amount'   => [
                        'amount'   => ($this->formatPrice($item->getPrice() * $item->getQuantity())) * -1,
                        'currency' => $this->getCurrency(),
                    ],
                ];
            }
        }

        return $discountsArray;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/checkouts';
    }

    /**
     * @param mixed $data
     * @return \Omnipay\AfterPay\Message\Response
     */
    protected function createResponse($data): AbstractResponse
    {
        return new PurchaseResponse($this, $data);
    }

    /**
     * @param string|float|int $amount
     * @return null|string
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    protected function formatPrice($amount)
    {
        $amount = $amount ?? 0.00;
        if ($amount) {
            if (!is_float($amount) &&
                $this->getCurrencyDecimalPlaces() > 0 &&
                false === strpos((string) $amount, '.')
            ) {
                throw new InvalidRequestException(
                    'Please specify amount as a string or float, ' .
                    'with decimal places (e.g. \'10.00\' to represent $10.00).'
                );
            }

        }
        
        return $this->formatCurrency($amount);
    }
}
