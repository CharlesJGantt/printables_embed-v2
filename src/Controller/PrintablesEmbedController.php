<?php

namespace Drupal\printables_embed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Returns responses for Printables Embed routes.
 */
class PrintablesEmbedController extends ControllerBase {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a PrintablesEmbedController.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(ClientInterface $http_client, CacheBackendInterface $cache_backend, LoggerInterface $logger, RendererInterface $renderer) {
    $this->httpClient = $http_client;
    $this->cacheBackend = $cache_backend;
    $this->logger = $logger;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('cache.default'),
      $container->get('logger.factory')->get('printables_embed'),
      $container->get('renderer')
    );
  }

  /**
   * Fetches data for a Printables model and returns HTML.
   *
   * @param string $model_id
   *   The Printables model ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function fetchModel($model_id) {
    // Check if data is in cache
    $cid = 'printables_embed:' . $model_id;
    $cache = $this->cacheBackend->get($cid);
    
    if ($cache && $cache->data) {
      $model_data = $cache->data;
    }
    else {
      // Fetch data from Printables GraphQL API
      $model_data = $this->fetchModelData($model_id);
      
      // Cache the data for 6 hours if we got valid data
      if (!empty($model_data)) {
        $this->cacheBackend->set(
          $cid,
          $model_data,
          time() + (6 * 60 * 60),
          ['printables_embed:' . $model_id]
        );
      }
    }
    
    // Build render array
    if (!empty($model_data)) {
      $build = [
        '#theme' => 'printables_embed',
        '#name' => $model_data['name'] ?? 'Unknown Model',
        '#summary' => $model_data['summary'] ?? '',
        '#author' => $model_data['user']['publicUsername'] ?? 'Unknown Author',
        '#author_avatar' => isset($model_data['user']['avatarFilePath']) ? 
          'https://media.printables.com/' . $model_data['user']['avatarFilePath'] : '',
        '#image_url' => isset($model_data['image']['filePath']) ?
          'https://media.printables.com/' . $model_data['image']['filePath'] : '',
        '#likes_count' => $model_data['likesCount'] ?? 0,
        '#download_count' => $model_data['downloadCount'] ?? 0,
        '#view_count' => $model_data['displayCount'] ?? 0,
        '#model_url' => 'https://www.printables.com/model/' . $model_id . '-' . ($model_data['slug'] ?? 'model'),
        '#attached' => [
          'library' => [
            'printables_embed/printables-embed',
          ],
        ],
      ];
      
      // Render HTML
      $html = $this->renderer->render($build);
      
      return new JsonResponse(['html' => $html]);
    }
    
    return new JsonResponse(['html' => '']);
  }

  /**
   * Fetch model data from Printables GraphQL API.
   *
   * @param string $model_id
   *   The Printables model ID.
   *
   * @return array|null
   *   The model data or NULL if not found.
   */
  protected function fetchModelData($model_id) {
    try {
      // GraphQL query
      $query = <<<GRAPHQL
query PrintProfile(\$id: ID!) {
  print(id: \$id) {
    id
    slug
    name
    summary
    user {
      id
      publicUsername
      avatarFilePath
    }
    likesCount
    downloadCount
    displayCount
    image {
      filePath
    }
  }
}
GRAPHQL;

      $response = $this->httpClient->post('https://api.printables.com/graphql/', [
        'json' => [
          'operationName' => 'PrintProfile',
          'query' => $query,
          'variables' => [
            'id' => $model_id,
          ],
        ],
        'headers' => [
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ],
      ]);

      $data = json_decode($response->getBody(), TRUE);
      
      if (isset($data['data']['print'])) {
        return $data['data']['print'];
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to fetch Printables model data: @error', [
        '@error' => $e->getMessage(),
      ]);
    }

    return NULL;
  }
}