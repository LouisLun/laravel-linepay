<?php
namespace Louis\LaravelLinepay\Contracts;

interface INonceProvider
{
    /**
     * generate nonce
     *
     * @return string
     */
    public function generate();
}