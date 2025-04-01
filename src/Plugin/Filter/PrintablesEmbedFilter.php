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
 *   type = \Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class PrintablesEmbedFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (preg_match_all('/<div class="printables-embed" data-printables-url="([^"]*)" data-printables-id="([^"]*)">[^<]*<\/div>/i', $text, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        $url = $match[1];
        $model_id = $match[2];
        $model_data = $this->getModelData($model_id);

        if ($model_data) {
          $embed = $this->generateEmbed($model_data, $url, $model_id);
          $text = str_replace($match[0], $embed, $text);
        }
      }

      $result->setProcessedText($text);
    }

    return $result;
  }

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

  protected function generateEmbed($model_data, $url, $model_id) {
    $width = 640;
    $height = 190;

    $embed = '<div class="printables-embed" style="width: ' . $width . 'px; height: ' . $height . 'px;">';
    $embed .= '<div class="thumbnail" style="background-image: url(\'https://media.printables.com/' . $model_data['image']['filePath'] . '\'); width: 190px; height: 190px;"></div>';
    $embed .= '<div class="content">';
    $embed .= '<div class="header">';
    $embed .= '<h2 class="model-name">' . htmlspecialchars($model_data['name']) . '</h2>';
    $embed .= '<a href="' . htmlspecialchars($url) . '" target="_blank" class="logo-container">';
    $embed .= '<img src="/modules/printables_embed/images/printables-logo.png" alt="Printables" class="printables-logo-img">';
    $embed .= '</a></div>';

    $embed .= '<div class="author">';
    $embed .= '<img class="author-avatar" src="https://media.printables.com/' . $model_data['user']['avatarFilePath'] . '" alt="' . htmlspecialchars($model_data['user']['publicUsername']) . '">';
    $embed .= 'by ' . htmlspecialchars($model_data['user']['publicUsername']);
    $embed .= '</div>';

    if (!empty($model_data['summary'])) {
      $embed .= '<div class="summary">' . htmlspecialchars($model_data['summary']) . '</div>';
    }

    $embed .= '<div class="footer"><div class="stats">';
    $embed .= '<div class="stat"><svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="..."/></svg>' . $model_data['likesCount'] . '</div>';
    $embed .= '<div class="stat"><svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="..."/></svg>' . $model_data['downloadCount'] . '</div>';
    $embed .= '<div class="stat"><svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="..."/></svg>' . $model_data['displayCount'] . '</div>';
    $embed .= '</div><a href="' . htmlspecialchars($url) . '" target="_blank" class="view-button">View On Printables.com</a></div>';
    $embed .= '</div></div>';

    return $embed;
  }
}
