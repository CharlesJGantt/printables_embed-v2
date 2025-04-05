<?php

namespace Drupal\printables_embed\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Html;

/**
 * Provides a filter to display Printables embeds.
 *
 * @Filter(
 *   id = "printables_embed",
 *   title = @Translation("Printables Embed"),
 *   description = @Translation("Converts Printables embed placeholders to embeds."),
 *   type = \Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class PrintablesEmbedFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    
    // If there's no DIV with class printables-embed, return early
    if (strpos($text, 'printables-embed') === FALSE) {
      return $result;
    }
    
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    
    // Find all printables embed divs
    $embeds = $xpath->query('//div[contains(@class, "printables-embed")]');
    
    if ($embeds->length > 0) {
      $changed = FALSE;
      
      foreach ($embeds as $embed) {
        $url = $embed->getAttribute('data-printables-url');
        $model_id = $embed->getAttribute('data-printables-id');
        
        if (!empty($model_id)) {
          $model_data = $this->getModelData($model_id);
          
          if (!empty($model_data)) {
            // Create a placeholder for the embed with the model ID
            $placeholder = "<div data-printables-embed=\"$model_id\"></div>";
            
            // Replace the original node with the placeholder
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($placeholder);
            $embed->parentNode->replaceChild($fragment, $embed);
            
            // Add model data as an asset library setting
            $key = 'printables_embed.model.' . $model_id;
            $result->addAttachments([
              'library' => ['printables_embed/printables-embed'],
              'drupalSettings' => [
                'printablesEmbed' => [
                  'models' => [
                    $model_id => [
                      'name' => $model_data['name'],
                      'summary' => $model_data['summary'] ?? '',
                      'author' => $model_data['user']['publicUsername'] ?? 'Unknown Author',
                      'authorAvatar' => isset($model_data['user']['avatarFilePath']) ? 
                        'https://media.printables.com/' . $model_data['user']['avatarFilePath'] : '',
                      'imageUrl' => isset($model_data['image']['filePath']) ?
                        'https://media.printables.com/' . $model_data['image']['filePath'] : '',
                      'likesCount' => $model_data['likesCount'] ?? 0,
                      'downloadCount' => $model_data['downloadCount'] ?? 0,
                      'viewCount' => $model_data['displayCount'] ?? 0,
                      'modelUrl' => 'https://www.printables.com/model/' . $model_id . '-' . ($model_data['slug'] ?? 'model'),
                    ],
                  ],
                ],
              ],
            ]);
            
            $changed = TRUE;
          }
        }
      }
      
      if ($changed) {
        $result->setProcessedText(Html::serialize($dom));
      }
    }
    
    return $result;
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
  protected function getModelData($model_id) {
    $cid = 'printables_embed:' . $model_id;
    $cache = \Drupal::cache()->get($cid);
    
    if ($cache && $cache->data) {
      return $cache->data;
    }
    
    try {
      $client = \Drupal::httpClient();
      
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
      
      $response = $client->post('https://api.printables.com/graphql/', [
        'json' => [
          'operationName' => 'PrintProfile',
          'query' => $query,
          'variables' => ['id' => $model_id],
        ],
        'headers' => [
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ],
      ]);
      
      $data = json_decode($response->getBody(), TRUE);
      
      if (isset($data['data']['print'])) {
        \Drupal::cache()->set(
          $cid,
          $data['data']['print'],
          time() + (6 * 60 * 60),
          ['printables_embed:' . $model_id]
        );
        
        return $data['data']['print'];
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('printables_embed')->error('Error fetching Printables data: @error', [
        '@error' => $e->getMessage(),
      ]);
    }
    
    return NULL;
  }
}