<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Guest;
use Psr\Log\LoggerInterface;

class GuestReportService
{
    private const JSON_FORMAT = 2; // 1 = XML, 2 = JSON
    private const METHOD = 'oddajPorocilo';
    private const WSDL = 'https://wwwt.ajpes.si/rno/rnoApi/eTurizem/wsETurizemPorocanje.asmx?WSDL';
    private \SoapClient $client;

    public function __construct(
        private readonly CityTaxCalculatorService $cityTaxCalculatorService,
        private readonly LoggerInterface $logger,
    ) {
        $this->client = new \SoapClient(self::WSDL, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
//            'local_cert' => dirname(__FILE__) . '/client-cert.pem',
//            'local_pk' => dirname(__FILE__) . '/client-key.pem',
//            'passphrase' => '123456',
        ]);
    }

    /**
     * @param Guest[] $guests
     */
    public function reportGuests(array $guests, string $username, string $password, int $idNO): void
    {
        $namespace = 'http://www.ajpes.si/eturizem/';

        $options = [
            'uName' => $username,
            'pwd' => $password,
            'data' => new \SoapVar($this->buildDataXml($namespace, $guests, $idNO), XSD_ANYXML),
            'format' => self::JSON_FORMAT
        ];

        try {
            $response = $this->client->__soapCall(self::METHOD, [$options]);
            $responseData = json_decode($response->oddajPorociloResult, true);
            if (isset($responseData['data']['@success']) && $responseData['data']['@success'] === '0') {
                foreach ($responseData['data']['row'] as $row) {
                    $this->logger->error(
                        sprintf('Error: %s - %s (ID: %s)', $row['@msg'], $row['@msgTxt'], $row['@id'])
                    );
                }
            }
        } catch (\SoapFault $e) {
            $this->logger->error(sprintf('SOAP Fault: %s', $e->getMessage()));
            return;
        }
    }

    private function buildDataXml(string $namespace, array $guests, int $propertyId): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $data = $doc->createElementNS($namespace, 'data');
        $doc->appendChild($data);

        $guestBook = $doc->createElement('knjigaGostov');
        $data->appendChild($guestBook);

        foreach ($guests as $guest) {
            $this->buildRow($doc, $guestBook, $guest, $propertyId);
        }

        return $doc->saveXML($data);
    }

    private function buildRow(\DOMDocument $doc, \DOMElement $guestBook, Guest $guest, int $propertyId): void
    {
        $checkIdDate = $guest->getCheckInDate()->format('Y-m-d') . 'T' .
            $guest->getClient()->getCheckInTime()->format('H:i:s');

        $checkOutDate = $guest->getCheckOutDate()->format('Y-m-d') . 'T' .
            $guest->getCheckOutTime()->format('H:i:s');

        $row = $doc->createElement('row');

        $row->setAttribute('idNO', (string) $propertyId);
        $row->setAttribute('zst', (string) $guest->getId());
        $row->setAttribute('ime', $guest->getFirstName());
        $row->setAttribute('pri', $guest->getLastName());
        $row->setAttribute('sp', $guest->getGender()->value);
        $row->setAttribute('dtRoj', $guest->getDateOfBirth()->format('Y-m-d'));
        $row->setAttribute('drzava', $guest->getNationality());
        $row->setAttribute('vrstaDok', $guest->getDocumentType()->value);
        $row->setAttribute('idStDok', $guest->getDocumentNumber());
        $row->setAttribute('casPrihoda', $checkIdDate);
        $row->setAttribute('casOdhoda', $checkOutDate);
        $row->setAttribute('status', '1');
        $row->setAttribute('ttObracun', (string) $guest->getCityTaxExemption());
        $row->setAttribute('ttVisina', (string) $this->cityTaxCalculatorService->calculateTax($this->getAges($guest)));

        $guestBook->appendChild($row);
    }

    private function getAges(Guest $guest): int
    {
        return (new \DateTime())->diff($guest->getDateOfBirth())->y;
    }
}
