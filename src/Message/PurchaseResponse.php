<?php

namespace Omnipay\AfterPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * @inheritDoc
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isRedirect()
    {
        $data = $this->getData();
        return isset($data['redirectCheckoutUrl']);
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUrl()
    {
        $data = $this->getData();
        return $data['redirectCheckoutUrl'] ?? null;
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
     * @inheritDoc
     */
    public function getExpires()
    {
        $data = $this->getData();
        return $data['expires'] ?? null;
    }

    // TODO: Remove all old code if it's no longer required.
//     protected $liveScript = 'https://www.secure-afterpay.com.au/afterpay.js';
//     protected $testScript = 'https://www-sandbox.secure-afterpay.com.au/afterpay.js';

//     /**
//      * @return bool
//      */
//     public function isRedirect()
//     {
//         return true;
//     }

//     /**
//      * @return \Symfony\Component\HttpFoundation\Response
//      */
//     public function getRedirectResponse()
//     {
//         $output = <<<EOF
// <html>
// <head>
//     <title>Redirecting...</title>
//     <script src="%s" async></script>
// </head>
// <body>
//     <script>
//     window.onload = function() {
//         AfterPay.init();
//         AfterPay.redirect({token: "%s"});
//     };
//     </script>
// </body>
// </html>
// EOF;

//         $output = sprintf($output, $this->getScriptUrl(), $this->getToken());

//         return HttpResponse::create($output);
//     }

//     /**
//      * @return string
//      */
//     public function getScriptUrl()
//     {
//         $request = $this->getRequest();

//         if ($request instanceof PurchaseRequest && $request->getTestMode()) {
//             return $this->testScript;
//         }

//         return $this->liveScript;
//     }

//     /**
//      * @return string|null
//      */
//     public function getToken()
//     {
//         return isset($this->data['token']) ? $this->data['token'] : null;
//     }

    /**
     * @return string
     */
    public function getTransactionReference()
    {
        // TODO: Reconsider if the `token` field would be appropriate for this as it's probably supposed to be secret.
        return $this->getToken();
    }
}
