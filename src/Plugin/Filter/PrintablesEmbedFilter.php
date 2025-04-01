<?php

namespace Drupal\printables_embed\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to display Printables embeds.
 *
 * @Filter(
 *   id = "printables_embed",
 *   title = @Translation("Printables Embed"),
 *   description = @Translation("Converts Printables embed placeholders to embeds."),
 *   type = \Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   settings = {
 *   }
 * )
 */
/**
 * {@inheritdoc}
 */
class PrintablesEmbedFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    
    // Process <div class="printables-embed"...> tags
    if (preg_match_all('/<div class="printables-embed" data-printables-url="([^"]*)" data-printables-id="([^"]*)">[^<]*<\/div>/i', $text, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        $url = $match[1];
        $model_id = $match[2];
        
        // Get model data
        $model_data = $this->getModelData($model_id);
        
        if ($model_data) {
          // Generate embed HTML
          $embed = $this->generateEmbed($model_data, $url, $model_id);
          
          // Replace placeholder with embed
          $text = str_replace($match[0], $embed, $text);
        }
      }
      
      $result->setProcessedText($text);
    }
    
    return $result;
  }
  
  /**
   * Get model data from cache or API.
   */
  protected function getModelData($model_id) {
    // Check cache
    $cid = 'printables_embed:' . $model_id;
    $cache = \Drupal::cache()->get($cid);
    
    if ($cache && $cache->data) {
      return $cache->data;
    }
    
    // Fetch from API
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
        // Cache for 6 hours
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
  
  /**
   * Generate embed HTML.
   */
  protected function generateEmbed($model_data, $url, $model_id) {
    $width = $this->settings['width'];
    $height = $this->settings['height'];
    
    // Start building embed
    $embed = '<div class="printables-embed" style="width: ' . $width . 'px; height: ' . $height . 'px;">';
    $embed .= '<div class="thumbnail" style="background-image: url(\'https://media.printables.com/' . $model_data['image']['filePath'] . '\'); width: 190px; height: 190px;"></div>';
    $embed .= '<div class="content">';
    
    // Header with name and logo
    $embed .= '<div class="header">';
    $embed .= '<h2 class="model-name">' . htmlspecialchars($model_data['name']) . '</h2>';
    $embed .= '<a href="' . htmlspecialchars($url) . '" target="_blank" class="logo-container">';
    $embed .= '<img src="/modules/printables_embed/images/printables-logo.png" alt="Printables" class="printables-logo-img">';
    $embed .= '</a>';
    $embed .= '</div>';
    
    // Author
    $embed .= '<div class="author">';
    $embed .= '<img class="author-avatar" src="https://media.printables.com/' . $model_data['user']['avatarFilePath'] . '" alt="' . htmlspecialchars($model_data['user']['publicUsername']) . '">';
    $embed .= 'by ' . htmlspecialchars($model_data['user']['publicUsername']);
    $embed .= '</div>';
    
    // Summary
    if (!empty($model_data['summary'])) {
      $embed .= '<div class="summary">' . htmlspecialchars($model_data['summary']) . '</div>';
    }
    
    // Stats and button
    $embed .= '<div class="footer">';
    $embed .= '<div class="stats">';
    
    // Add stats with SVG icons
    $embed .= '<div class="stat">';
    $embed .= '<svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>';
    $embed .= $model_data['likesCount'];
    $embed .= '</div>';
    
    $embed .= '<div class="stat">';
    $embed .= '<svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>';
    $embed .= $model_data['downloadCount'];
    $embed .= '</div>';
    
    $embed .= '<div class="stat">';
    $embed .= '<svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>';
    $embed .= $model_data['displayCount'];
    $embed .= '</div>';
    
    $embed .= '</div>';
    
    // Add button
    $embed .= '<a href="' . htmlspecialchars($url) . '" target="_blank" class="view-button">View On Printables.com</a>';
    $embed .= '</div>'; // Close footer
    $embed .= '</div>'; // Close content
    $embed .= '</div>'; // Close printables-embed
    
    return $embed;
  }
  
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#default_value' => $this->settings['width'],
      '#min' => 100,
      '#max' => 1200,
    ];
    
    $form['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#default_value' => $this->settings['height'],
      '#min' => 100,
      '#max' => 800,
    ];
    
    return $form;
  }
}
public function defaultSettings() {
  return [
    'width' => 640,
    'height' => 190,
  ] + parent::defaultSettings();
}