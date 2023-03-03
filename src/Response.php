<?php
namespace Louis\LaravelLinepay;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Response implements Arrayable, ArrayAccess, Jsonable
{
    /**
     * response
     *
     * @var \GuzzleHttp\Psr7\Response
     */
    public $response;

    /**
     * transfer stats
     *
     * @var \GuzzleHttp\TransferStats
     */
    public $stats;

    /**
     * body array cache
     *
     * @var array
     */
    protected $bodyArrayCache;

    /**
     * constructor
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @param \GuzzleHttp\TransferStats|null $stats
     * @return self
     */
    public function __construct(\GuzzleHttp\Psr7\Response $response, \GuzzleHttp\TransferStats|null $stats = null)
    {
        $this->response = $response;
        $this->stats = $stats;
    }

    /**
     * Get returnCode
     *
     * @return string
     */
    public function getReturnCode()
    {
        return $this->offsetGet('returnCode');
    }

    /**
     * Get Info
     *
     * @return mixed
     */
    public function getInfo()
    {
        return $this->offsetGet('info');
    }

    /**
     * Get returnMessage
     *
     * @return string
     */
    public function getReturnMessage()
    {
        return $this->offsetGet('returnMessage');
    }

    /**
     * check the resposne is success
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getReturnCode() == '0000';
    }

    /**
     * @return \GuzzleHttp\TransferStats|null
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * return response data array
     *
     * @return array
     */
    public function toArray()
    {
        if (!$this->bodyArrayCache) {
            $this->bodyArrayCache = json_decode($this->response->getBody(), true, 512, JSON_BIGINT_AS_STRING);
        }

        return $this->bodyArrayCache;
    }

    /**
     * return response data array
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        return;
    }

    public function offsetUnset(mixed $offset): void
    {
        return;
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }
}
