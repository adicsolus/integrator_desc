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
        if ($request->isMethod('SET')) {
            $productId = $request->request->get('product_name');
            $productName = $this->getProductNameFromCSV($productId);
            $opis = $description->getDescription($productName);
            
            
            foreach ($opisy as $fragmentNazwy => $opis) {
                if (strpos($productName, $fragmentNazwy) !== false) {
                    $description = "<p>$opis</p>";
                    break;
                }
            }
        }
            $client = HttpClient::create();
            $response = $client->request('PUT', 'https://devshop-544897.shoparena.pl/webapi/rest/products/'.$productId.'/description', [
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
        return new JsonResponse(['error' => 'Invalid request method'], 400);
    }
    private function getProductNameFromCSV(int $productId): string
    {
        $csvFile = 'nazwa_pliku.csv';
        $csvReader = new CsvReader($csvFile);

        // Przeszukaj plik CSV w poszukiwaniu odpowiedniego identyfikatora produktu
        foreach ($csvReader as $row) {
            if ($row[0] == $productId) {
                return $row[1]; // Pobierz nazwę produktu
            }
        }
          // Jeśli nie znaleziono produktu o podanym identyfikatorze, zwróć pusty ciąg znaków lub obsłuż błąd
          throw new \Exception('Product not found');
    }

    private function getAccessToken(): string
    {
        $client = HttpClient::create();

        $response = $client->request('POST', 'https://devshop-544897.shoparena.pl/webapi/rest/auth', [
            'headers' => [
                'Authorization' => 'Basic '.base64_encode('admin:Skyen12#'),
                'Content-Type' => 'application/json',
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $content = $response->getContent();
            $data = json_decode($content, true);
            $accessToken = $data['access_token'];
            return $accessToken;
        } else {
            throw new \Exception('Nie udało się uzyskać access tokena');
        }
    }
}    
?>