<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider\OAuth1\Signature;

use SocialConnect\Auth\Provider\Consumer;
use SocialConnect\Auth\Provider\OAuth1\Request;
use SocialConnect\Auth\Provider\OAuth1\Token;
use SocialConnect\Auth\Provider\OAuth1\Util;

class MethodHMACSHA1 extends AbstractSignatureMethod
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'HMAC-SHA1';
    }

    /**
     * @param Request $request
     * @param Consumer $consumer
     * @param Token $token
     * @return string
     */
    public function buildSignature(Request $request, Consumer $consumer, Token $token)
    {
        $signatureBase = $request->getSignatureBaseString();
        $parts = array($consumer->getSecret(), null !== $token ? $token->getSecret() : '');

        $parts = Util::urlencodeRFC3986($parts);
        $key = implode('&', $parts);

        return base64_encode(hash_hmac('sha1', $signatureBase, $key, true));
    }
}
