<?php
namespace Louis\LaravelLinepay;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Louis\LaravelLinepay\Exceptions\LinepayException;
use Illuminate\Support\Str;
use Louis\LaravelLinepay\Contracts\INonceProvider;
use Louis\LaravelLinepay\Exceptions\LinepayConnectExcetpion;

/**
 * Line Pay Client
 *
 * @author Louis Zhan <louis.zhan.tidy@gmail.com>
 * @version 3.0.0
 */
class Linepay
{
    /**
     * Line Pay base API host
     */
    const API_HOST = 'https://api-pay.line.me';

    /**
     * Line Pay Sandbox base API host
     */
    const SANDBOX_API_HOST = 'https://sandbox-api-pay.line.me';

    /**
     * LINE Pay API URI list
     *
     * @var array
     */
    protected static $apiUris = [
        'request' => '/v3/payments/request',
        'confirm' => '/v3/payments/{transactionId}/confirm',
        'refund' => '/v3/payments/{transactionId}/refund',
        'details' => '/v3/payments',
        'check' => '/v3/payments/requests/{transactionId}/check',
        'capture' => '/v3/payments/authorizations/{transactionId}/capture',
        'void' => '/v3/payments/authorizations/{transactionId}/void',
        'preapproved' => '/v3/payments/preapprovedPay/{regKey}/payment',
        'preapprovedCheck' => '/v3/payments/preapprovedPay/{regKey}/check',
        'preapprovedExpire' => '/v3/payments/preapprovedPay/{regKey}/expire',
    ];

    /**
     * config
     *
     * @var array
     */
    protected $config;

    /**
     * HTTP Client
     *
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Set LINE Pay Channel Secret for v3 API Authentication
     *
     * @var string
     */
    protected $channelSecret;

    /**
     * Set function by generating nonce
     *
     * @var \Louis\LaravelLinepay\Contracts\INonceProvider
     */
    protected $nonceProvider;

    /**
     * Laravel Logger
     *
     * @var Illuminate\Log\Logger
     */
    protected $logger;

    /**
     * constructor
     *
     * @param array $config API Key
     *  'channelId' => Your merchant X-LINE-ChannelId
     *  'channelSecret' => Your merchant X-LINE-ChannelSecret
     *  'isSandbox' => Sandbox mode
     *  'nonceProvider' => nonce provider
     * @return self
     */
    public function __construct($config = [])
    {
        dd($config);
        $this->config = $config;
        $channelId = $config['channelId'] ?? null;
        $channelSecret = $config['channelSecret'] ?? null;
        $merchantDeviceType = $config['merchantDeviceType'] ?? null;
        $merchantDeviceProfileId = $config['merchantDeviceProfileId'] ?? null;
        $isSandbox = $config['isSandbox'] ?? false;
        $debug = $config['debug'] ?? false;
        if (isset($config['nonceProvider']) && ($config['nonceProvider'] instanceof INonceProvider)) {
            $this->nonceProvider = $config['nonceProvider'];
        }
        // $this->logger = $config['logger'] ?? null;

        if (!$channelId || !$channelSecret) {
            throw new LinepayException('the channelId or the channelSecret are required', 400);
        }

        // Base URI
        $baseUri = ($isSandbox) ? self::SANDBOX_API_HOST : self::API_HOST;

        // Headers
        $headers = [
            'Content-Type' => 'application/json',
            'X-Line-ChannelId' => $channelId,
        ];
        // Set channel secret
        $this->channelSecret = $channelSecret;

        // MerchantDeviceType
        if ($merchantDeviceType) {
            $headers['X-LINE-MerchantDeviceType'] = $merchantDeviceType;
        }
        // MerchantDeviceProfileId
        if ($merchantDeviceProfileId) {
            $headers['X-LINE-MerchantDeviceProfileId'] = $merchantDeviceProfileId;
        }

        $this->httpClient = new Client([
            'base_uri' => $baseUri,
            'headers' => $headers,
            'http_errors' => false,
            'debug' => $debug,
        ]);

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get LINE Pay signature for authentication
     *
     * @param string $channelSecret
     * @param string $uri
     * @param string $queryOrBody
     * @param string $nonce
     * @return string
     */
    public static function getAuthSignature($channelSecret, $uri, $queryOrBody, $nonce)
    {
        $authMacText = $channelSecret . $uri . $queryOrBody . $nonce;
        return base64_encode(hash_hmac('sha256', $authMacText, $channelSecret, true));
    }

    /**
     * request API
     *
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function request($params)
    {
        return $this->requestHandler('POST', $this->getAPIUri('request'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * confirm API
     *
     * @param string $transactionId
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function confirm($transactionId, $params)
    {
        return $this->requestHandler('POST', str_replace('{transactionId}', $transactionId, $this->getAPIUri('confirm')), $params, [
            'connect_timeout' => 5,
            'timeout' => 40,
        ]);
    }

    /**
     * capture API
     *
     * @param string $transactionId
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function capture($transactionId, $params)
    {
        return $this->requestHandler('POST', str_replace('{transactionId}', $transactionId, $this->getAPIUri('capture')), $params, [
            'connect_timeout' => 5,
            'timeout' => 40,
        ]);
    }

    /**
     * refund API
     *
     * @param string $transactionId
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function refund($transactionId, $params)
    {
        return $this->requestHandler('POST', str_replace('{transactionId}', $transactionId, $this->getAPIUri('refund')), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * void API
     *
     * @param string $transactionId
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function void($transactionId, $params)
    {
        return $this->requestHandler('POST', str_replace('{transactionId}', $transactionId, $this->getAPIUri('void')), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * payment details API
     *
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function details($params)
    {
        return $this->requestHandler('GET', $this->getAPIUri('details'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * Check Payment Status API
     *
     * @param string $transactionId
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function check($transactionId, $params)
    {
        return $this->requestHandler('GET', str_replace('{transactionId}', $transactionId, $this->getAPIUri('check')), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * Pay Preapproved API
     *
     * @param string $regKey
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function preapproved($regKey, $params)
    {
        return $this->requestHandler('POST', str_replace('{regKey}', $regKey, $this->getAPIUri('preapproved')), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * Check RegKey API
     *
     * @param string $regKey
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function preapprovedCheck($regKey, $params)
    {
        return $this->requestHandler('GET', str_replace('{regKey}', $regKey, $this->getAPIUri('preapprovedCheck')), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * Expire RegKey API
     *
     * @param string $regKey
     * @param array $params
     * @return \Louis\LaravelLinepay\Response
     */
    public function preapprovedExpire($regKey, $params)
    {
        return $this->requestHandler('POST', str_replace('{regKey}', $regKey, $this->getAPIUri('preapprovedExpire')), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * api uri
     *
     * @param string $key
     * @return string
     */
    protected function getAPIUri($key)
    {
        return self::$apiUris[$key];
    }

    /**
     * http client
     *
     * @return \GuzzleHttp\Client
     */
    public function client()
    {
        return $this->httpClient;
    }

    /**
     * request handler
     *
     * @param string $method method
     * @param string $uri
     * @param array $params
     * @param array $options
     * @return \Louis\LaravelLinepay\Response
     */
    public function requestHandler($method, $uri, array $params = [], $options = [])
    {
        $headers = [];

        $authParams = '';
        $url = $uri;
        $body = '';
        if ($method == 'GET') {
            $authParams = http_build_query($params);
            $url = "$uri?$authParams";
        } else {
            $authParams = json_encode($params);
            $body = $authParams;
        }
        $nonce = $this->nonceProvider ? $this->nonceProvider->generate() : microtime(true) * 1000 . Str::uuid();
        $headers['X-LINE-Authorization'] = static::getAuthSignature($this->channelSecret, $uri, $authParams, $nonce);
        $headers['X-LINE-Authorization-Nonce'] = $nonce;

        $stats = null;
        $options['on_stats'] = function (\GuzzleHttp\TransferStats $transferStats) use (&$stats) {
            $stats = $transferStats;
        };

        $request = new Request($method, $url, $headers, $body);
        try {
            $response = $this->client()->send($request, $options);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new LinepayConnectExcetpion($e->getMessage(), $e->getCode(), $e->getPrevious(), $e->getHandlerContext());
        }

        return new Response($response, $stats);
    }
}
