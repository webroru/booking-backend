<?php

declare(strict_types=1);

namespace App\Factory;

use SoapClient;

class SoapClientFactory
{
    public function create(string $wsdlPath, string $certPath, string $passphrase): SoapClient
    {
        return new SoapClient($wsdlPath, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'local_cert' => $certPath,
            'passphrase' => $passphrase,
        ]);
    }
}
