<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\Guest;
use App\Exception\GuestReportException;
use App\Repository\GuestRepository;
use Psr\Log\LoggerInterface;
use SoapClient;

class GuestReportService
{
    private const JSON_FORMAT = 2; // 1 = XML, 2 = JSON
    private const METHOD = 'oddajPorocilo';

    public function __construct(
        private readonly CityTaxCalculatorService $cityTaxCalculatorService,
        private readonly GuestRepository $guestRepository,
        private readonly SoapClient $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param Guest[] $guests
     * @throws GuestReportException
     */
    public function reportGuests(array $guests, string $username, string $password): void
    {
        $data = new \stdClass();
        $data->any = $this->buildDataXml($guests);

        $options = [
            'uName' => $username,
            'pwd' => $password,
            'data' => $data,
            'format' => self::JSON_FORMAT
        ];

        try {
            $response = $this->client->__soapCall(self::METHOD, [$options]);
            $responseData = json_decode($response->oddajPorociloResult, true);
            $this->handleRequest($responseData);
        } catch (\SoapFault $e) {
            $error = sprintf('SOAP Fault: %s', $e->getMessage());
            $this->logger->error($error);
            throw new GuestReportException($error);
        }
    }

    /**
     * @throws GuestReportException
     */
    public function reportByClient(Client $client): void
    {
        $guests = $this->guestRepository->findReadyForReportByClient($client);
        $this->reportGuests($guests, $client->getAjPesUsername(), $client->getAjPesPassword());
    }

    private function buildDataXml(array $guests): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');

        $guestBook = $doc->createElement('knjigaGostov');
        $doc->appendChild($guestBook);

        foreach ($guests as $guest) {
            $guestBook->appendChild($doc->importNode($this->buildRow($guest), true));
        }

        return $doc->saveXML($guestBook);
    }

    private function buildRow(Guest $guest): \DOMElement
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $row = $doc->createElement('row');

        $checkIdDate = $guest->getCheckInDate()->format('Y-m-d') . 'T' .
            $guest->getClient()->getCheckInTime()->format('H:i:s');

        $checkOutDate = $guest->getCheckOutDate()->format('Y-m-d') . 'T' .
            $guest->getCheckOutTime()->format('H:i:s');

        $row->setAttribute('idNO', (string) $guest->getRoom()->getGovernmentPortalId());
        $row->setAttribute('zst', (string) (1000 + $guest->getId()));
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

        return $row;
    }

    private function getAges(Guest $guest): int
    {
        return (new \DateTime())->diff($guest->getDateOfBirth())->y;
    }

    private function errorMapper(int $code): string
    {
        $errorMessages = [
            413 => 'Invalid user name or password.',
            500 => 'Process fails',
            4130 => 'XML document failed XSD validation schema',
            4131 => 'Accommodation facility does not exist',
            4132 => 'Accommodation facility inactive.',
            4133 => 'Guest serial no. lower than 0.',
            4134 => 'Guest name: at least 1 character.',
            4135 => 'Guest surname: at least 1 character.',
            4136 => 'Not born yet?',
            4137 => 'Older than 120 years?',
            4138 => 'Invalid country code.',
            4139 => 'Invalid document type.',
            4140 => 'Accommodation unit deleted from registry',
            4141 => 'Sold units exceeds available units.',
            4142 => 'A record on this accommodation unit with the same serial number and in the same year has been inserted by another user',
            4143 => 'The selected item is invalid with regard to the guest registration date',
            4144 => 'A record with the same data has already been transmitted.',
            4145 => 'Restriction of the date of arrival.',
            4146 => 'Restriction of the departure date.',
            4147 => 'Invalid tax amount.',
            20001 => 'Process report',
            41300 => 'Certificate expired',
            41301 => 'Invalid XML',
            41302 => 'The monthly report is not forwarded during the required period.',
            41311 => 'To many rows',
            41321 => 'Invalid status',
            41322 => 'The monthly report missing',
            41323 => 'Invalid year',
            41324 => 'Invalid month',
            41325 => 'The number of facitlity beds is lower than the number of extra beds',
            41326 => 'The number of days when facility was opened must be more than 0.',
            41327 => 'The number of days when facility was opened is greater than the number of days in month.',
            41328 => 'Arrival on future date.',
            41329 => 'The number of units sold exceeds the number of accommodation units in the register.',
            41330 => 'Incorrect gender of the guest.',
            41391 => 'The length of document number must be more then 1 character.',
            41392 => 'Arrival before departure?',
            41393 => 'Invalid tourist tax type code',
            41394 => 'The full amount of tourist tax must be more than 0.',
            41395 => 'XML already processed on',
            41396 => 'No records',
            41397 => 'User does not have authority for this facility.',
            41398 => 'Certificate not registered with Ajpes',
            41399 => 'Certificate is not valid yet',
        ];

        return $errorMessages[$code] ?? 'Unknown error';
    }

    private function handleRequest(array $responseData): void
    {
        if (isset($responseData['data']['@failure']) && (int) $responseData['data']['@failure'] > 0) {
            $this->handleErrors($responseData['data']['row']);
        }
        if (isset($responseData['data']['@success']) && (int) $responseData['data']['@success'] > 0) {
            $this->logger->info(
                sprintf('Guests data successfully sent. Package Guid: %s', $responseData['data']['@packageGuid'])
            );
        }
    }

    private function handleErrors(array $errors): void
    {
        $result = [];
        foreach ($errors as $error) {
            $message = $this->errorMapper((int) $error['@msg']);
            $this->logger->error(
                sprintf(
                    'Error: %s - %s (ID: %s), original error: %s',
                    $error['@msg'],
                    $message,
                    $error['@id'],
                    $error['@msgTxt'],
                )
            );
            $result[] = $message;
        }
        if (!empty($result)) {
            throw new GuestReportException(implode(', ', $result));
        }
    }
}
