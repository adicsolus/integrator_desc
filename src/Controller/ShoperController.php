<?php

namespace App\Controller;

require_once 'desc.php';
require_once 'dest.php';
require_once 'size.php';
require_once 'country.php';

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\Csv\CsvReader;

class ShoperDesc
{
    public function getDescription(string $productName): string
    {

        if (isset($this->$opis[$productName])) {
            return $this->$opisy[$productName];
        }
       
         return '';
    }
}      
class ShoperController extends AbstractController
{
    #[Route('/api/v1/products/descriptions', name: 'api_v1_descriptions')]
    public function descriptions(Request $request, ShoperDesc $shoperDesc): JsonResponse
{
    // Pobranie danych z żądania POST
    if ($request->isMethod('POST')) {
        $productIdFromShoper = $request->request->get('product_id');

        // Pobranie nazwy produktu z pliku CSV na podstawie ID produktu z Shoper
        $productName = $this->getProductNameFromCSV($productIdFromShoper);

        // Pobranie ID produktu z pliku CSV na podstawie nazwy produktu
        $productIdFromCSV = $this->getProductIdFromCSV($productName);

        // Sprawdzenie, czy ID produktu z Shoper i z pliku CSV się zgadzają
        if ($productIdFromShoper != $productIdFromCSV) {
            throw new \Exception('Product ID mismatch');
        }

        // Generowanie opisu na podstawie fragmentu nazwy produktu
        $opis = $shoperDesc->getDescription($productName);

        // Wyszukiwanie dopasowania opisu do fragmentu nazwy
        foreach ($opis as $fragmentNazwy => $opisProduktu) {
            if (strpos($productName, $fragmentNazwy) !== false) {
                $description = "<p>$opisProduktu</p>";
                break;
            }
        }

        // Wysłanie opisu do Shoper za pomocą API
        $accessToken = $this->getAccessToken();

        $client = HttpClient::create();
        $response = $client->request('PUT', 'https://devshop-544897.shoparena.pl/webapi/rest/products/'.$productIdFromShoper.'/description', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$accessToken,
            ],
            'json' => [
                'description' => $description,
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            return new JsonResponse(['success' => true], $statusCode);
        } else {
            return new JsonResponse(['success' => false], $statusCode);
        }
    }

    return new JsonResponse(['error' => 'Invalid request method'], 400);
}

private function getProductNameFromCSV(int $productIdFromShoper): string
{
    $csvFile = 'nazwa_pliku.csv';
    $csvReader = new CsvReader($csvFile);

    // Przeszukaj plik CSV w poszukiwaniu odpowiedniego identyfikatora produktu z Shoper
    foreach ($csvReader as $row) {
        if ($row[0] == $productIdFromShoper) {
            return $row[1]; // Pobierz nazwę produktu
        }
    }

    // Jeśli nie znaleziono produktu o podanym identyfikatorze, zwróć pusty ciąg znaków lub obsłuż błąd
    throw new \Exception('Product not found');
}

private function getProductIdFromCSV(string $productName): int
{
    $csvFile = 'nazwa_pliku.csv';
    $csvReader = new CsvReader($csvFile);

    // Przeszukaj plik CSV w poszukiwaniu odpowiedniej nazwy produktu
    foreach ($csvReader as $row) {
        if ($row[1] == $productName) {
            return $row[0]; // Pobierz ID produktu
        }
    }

    // Jeśli nie znaleziono produktu o podanej nazwie, zwróć 0 lub obsłuż błąd
    throw new \Exception('Product not found');
}
