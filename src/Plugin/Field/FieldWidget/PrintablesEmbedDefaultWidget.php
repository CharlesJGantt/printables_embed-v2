<?php

namespace Drupal\printables_embed\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'printables_embed_printables_url' widget.
 *
 * @FieldWidget(
 *   id = "printables_embed_printables_url",
 *   label = @Translation("Printables URL"),
 *   field_types = {
 *     "printables_embed"
 *   }
 * )
 */
class PrintablesEmbedDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Printables URL'),
      '#description' => $this->t('Enter a URL like https://www.printables.com/model/12345 or https://www.printables.com/embed/12345'),
      '#default_value' => isset($items[$delta]->url) ? $items[$delta]->url : NULL,
      '#maxlength' => 2048,
      '#required' => $element['#required'],
      '#placeholder' => 'https://www.printables.com/model/123456-model-name',
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      if (!empty($value['url'])) {
        // Ensure URL is properly formatted
        if (!preg_match('~^https?://~i', $value['url'])) {
          $value['url'] = 'https://' . $value['url'];
        }
      }
    }
    return $values;
  }

}