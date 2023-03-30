<?php
namespace LouisLun\LaravelLinepay\Contracts;

interface INonceProvider
{
    /**
     * generate nonce
     *
     * @return string
     */
    public function generate();
}