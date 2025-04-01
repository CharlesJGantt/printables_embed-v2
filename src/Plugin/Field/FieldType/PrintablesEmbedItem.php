<?php

namespace Drupal\printables_embed\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'printables_embed' field type.
 *
 * @FieldType(
 *   id = "printables_embed",
 *   label = @Translation("Printables Embed"),
 *   description = @Translation("Stores a Printables URL and displays model data from the Printables API."),
 *   default_widget = "printables_embed_printables_url",
 *   default_formatter = "printables_embed_printables_embed"
 * )
 */
class PrintablesEmbedItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'url' => [
          'type' => 'varchar',
          'length' => 2048,
          'not null' => FALSE,
        ],
        'model_id' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['url'] = DataDefinition::create('string')
      ->setLabel(t('Printables URL'));
    $properties['model_id'] = DataDefinition::create('string')
      ->setLabel(t('Model ID'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('url')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();
    
    // Extract model ID from URL
    $url = $this->get('url')->getValue();
    if (!empty($url)) {
      // Match IDs in formats like printables.com/model/12345 or printables.com/embed/12345
      if (preg_match('/(model|embed)\/(\d+)/', $url, $matches)) {
        $this->set('model_id', $matches[2]);
      }
    }
  }
}