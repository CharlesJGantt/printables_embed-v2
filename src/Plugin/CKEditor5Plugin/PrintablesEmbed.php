<?php

namespace Drupal\printables_embed\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginBase;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Printables Embed Button.
 */
class PrintablesEmbed extends CKEditor5PluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor) {
    $settings = $editor->getSettings();
    // Get the filter settings if available
    $filter_settings = [];
    if (isset($settings['plugins']['filter_printables_embed'])) {
      $filter_settings = $settings['plugins']['filter_printables_embed'];
    }
    
    return [
      'printables_embed' => [
        'buttonLabel' => $this->t('Insert Printables'),
        'dialogTitle' => $this->t('Insert Printables Embed'),
        'width' => $filter_settings['width'] ?? 640,
        'height' => $filter_settings['height'] ?? 190,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(EditorInterface $editor) {
    return [
      'printables_embed/ckeditor5',
    ];
  }

}