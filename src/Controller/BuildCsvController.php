<?php

namespace Drupal\gm_mailing_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;

/**
* Class BuildCsvController.
*
* @package Drupal\gm_mailing_list\Controller
*/
class BuildCsvController extends ControllerBase {


  /**
    * Export a CSV of data.
    */
  public function build() {
    // Start using PHP's built in file handler functions to create a temporary file.
    $handle = fopen('php://temp', 'w+');

    // Set up the header that will be displayed as the first line of the CSV file.
    // Blank strings are used for multi-cell values where there is a count of
    // the "keys" and a list of the keys with the count of their usage.
    $header = [
      'Article Title',
      'Article Status',
      'Article Author',
      'Referenced Users',
      ' ',
      'Term Reference Field on Users',
      ' ',
    ];
    // Add the header as the first line of the CSV.
    fputcsv($handle, $header);
    // Find and load all of the Article nodes we are going to include
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'evenement')
      ->condition('status', 1);
    $results = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($results);

    // Iterate through the nodes.  We want one row in the CSV per Article.
    foreach ($nodes as $node) {
      // Build the array for putting the row data together.
      $data = $this->buildRow($node);

      // Add the data we exported to the next line of the CSV>
      fputcsv($handle, array_values($data));
    }
    // Reset where we are in the CSV.
    rewind($handle);
    
    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.  We are not storing
    // this file anywhere in the filesystem.
    fclose($handle);

    // This is the "magic" part of the code.  Once the data is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "article-report.csv".
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="article-report.csv"');

    // This line physically adds the CSV data we created 
    $response->setContent($csv_data);

    return $response;
  }

  /**
    * Fetches data and builds CSV row.
    *
    * @param \Drupal\node\Entity\Node $node
    *   Article node.
    *
    * @return array
    *   Row data.
    *
    * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
    * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
    */
  private function buildRow(Node $node) {
    // $user_data = $this->getuserData($node);
    $data = [
      'name' => $node->label(),
      'status' => $node->get('status')->value,
      'author' => $node->getOwner()->getDisplayname(),
      'referenced_users_count' => 23,
      'referenced_users' => 'tristan',
      'user_term_field_count' => 234,
      'user_term_field' => 'hello',
    ];

    return $data;
  }

  /**
   * @return JsonResponse
   */
  public function findCodePostal(Request $request) {
    $results = [];
    $input = $request->query->get('q');
   
    if (!$input) {
      return new JsonResponse($results);
    }

    /** @var \GuzzleHttp\Client $client */
    $client = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => 'https://api-adresse.data.gouv.fr/',
    ]);

    $response = $client->get('search', [
      'query' => [
        'q' => 'postcode=' .$input,
        'limit' => 20,
      ]
    ]);

    $datas = Json::decode($response->getBody());
    $items = [];
    $array_sort_dispatche = [];
    $villes = (isset($datas['features']) ? $datas['features'] : NULL);
    if ($villes) {
      foreach ($villes as $ville) {
        $array_sort_dispatche[$ville['properties']['postcode']] = $ville['properties']['city'] . ' * ' .$ville['properties']['postcode'];
      }
     $array_sort = array_unique($array_sort_dispatche, SORT_REGULAR);
      foreach ($array_sort as $cd => $city) {
        $items[] = [
          'value' => $cd,
          'label' => $city,
        ];
      }
    }
    return new JsonResponse($items);
  }
}