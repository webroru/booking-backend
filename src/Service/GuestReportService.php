<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Guest;

class GuestReportService
{
    private \SoapClient $client;
    private string $uName;
    private string $pwd;

    public function __construct(string $wsdlUrl, string $uName, string $pwd)
    {
        $wsdlUrl = 'https://wwwt.ajpes.si/rno/rnoApi/doc_eTurizem/ePorocanje.aspx';
        //$wsdlUrl = 'https://wwwt.ajpes.si/rno/rnoApi/doc_eTurizem/eporocanje.aspx?WSDL';
        $this->uName = $uName;
        $this->pwd = $pwd;
        $this->client = new \SoapClient($wsdlUrl, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);
    }

    public function reportGuest(Guest $guest, int $idNO, int $zst): mixed
    {
        $xml = $this->buildGuestXml($guest, $idNO, $zst);

        $params = [
            'uName'  => $this->uName,
            'pwd'    => $this->pwd,
            'data'   => $xml,
            'format' => 1, // 1 = XML, 2 = JSON
        ];

        return $this->client->__soapCall('oddajPorocilo', [$params]);
    }

    private function buildGuestXml(Guest $guest, int $propertyId, int $guestId): string
    {
        $checkIdDate = $guest->getCheckInDate()->format('Y-m-d') . 'T' .
            $guest->getClient()->getCheckInTime()->format('H:i:s');

        $checkOutDate = $guest->getCheckOutDate()->format('Y-m-d') . 'T' .
            $guest->getCheckOutTime()->format('H:i:s');

        $xml = <<<XML
<knjigaGostov>
    <row idNO="$propertyId" zst="$guestId"
         ime="{$guest->getFirstName()}"
         pri="{$guest->getLastName()}"
         sp="{$guest->getGender()->value}"
         dtRoj="{$guest->getDateOfBirth()->format('Y-m-d')}"
         drzava="{$guest->getNationality()}"
         vrstaDok="{$guest->getDocumentType()->value}"
         idStDok="{$guest->getDocumentNumber()}"
         casPrihoda="$checkIdDate"
         casOdhoda="$checkOutDate"
         dtPrijave="{$guest->getCheckInDate()->format('Y-m-d')}"
         dtOdjave="{$guest->getCheckOutDate()->format('Y-m-d')}"
         ttObracun="{$guest->getCityTaxExemption()}"
         ttVisina="{$this->calculateTaxAmount($guest)}"/>
</knjigaGostov>
XML;

        return $xml;
    }

    private function calculateTaxAmount(Guest $guest): float
    {
        // Логика расчёта туристической таксы
        return 0.0;
    }
}
