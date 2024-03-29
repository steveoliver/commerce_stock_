<?php

namespace Drupal\commerce_stock_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'commerce_stock_level' widget.
 *
 * @FieldWidget(
 *   id = "commerce_stock_level_simple",
 *   module = "commerce_stock_field",
 *   label = @Translation("Simple stock level widget"),
 *   field_types = {
 *     "commerce_stock_level"
 *   }
 * )
 */
class SimpleStockLevelWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'transaction_note' => FALSE,
      'entry_system' => 'simple',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Entry system: @entry_system', ['@entry_system' => $this->getSetting('entry_system')]);
    $summary[] = $this->t('Transaction note: @transaction_note', ['@transaction_note' => $this->getSetting('transaction_note') ? 'Yes' : 'No']);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['entry_system'] = [
      '#type' => 'select',
      '#title' => $this->t('Entry system'),
      '#options' => [
        'simple' => $this->t('Simple (absolute stock level)'),
        'basic' => $this->t('Basic transactions'),
        'transactions' => $this->t('Use transactions (read only form)'),
      ],
      '#default_value' => $this->getSetting('entry_system'),
    ];

    $element['transaction_note'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Provide note'),
      '#default_value' => $this->getSetting('transaction_note'),
      '#description' => $this->t('Provide an input box for a transaction note.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Get the stock field.
    $field = $items->first();
    // Get the product entity.
    $entity = $items->getEntity();
    // Make sure entity is a product variation.
    if ($entity instanceof ProductVariationInterface) {
      // Get the available stock level.
      $level = $field->available_stock;
    }
    else {
      // No stock if this is not a product variation.
      $level = 0;
      return [];
    }
    $elements = [];

    // Check the Stock Entry system chosen.
    $entry_system = $this->getSetting('entry_system');

    $elements['stock'] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Stock'),
    ];
    if (empty($entity->id())) {
      // We don't have a product ID as yet.
      $elements['stock']['stock_label'] = [
        '#type' => 'html_tag',
        '#tag' => 'strong',
        '#value' => $this->t('In order to set the stock level you need to save the product first!'),
      ];
    }
    else {
      // Common.
      $elements['stock']['stocked_variation_id'] = [
        '#type' => 'value',
        '#value' => $entity->id(),
        '#element_validate' => [
          [$this, 'validateSimpleId'],
        ],
      ];

      // Simple entry system = One edit box for stock level.
      if ($entry_system == 'simple') {

        $elements['stock']['value'] = [
          '#description' => $this->t('Available stock.'),
          '#type' => 'textfield',
          '#default_value' => $level,
          '#size' => 10,
          '#maxlength' => 12,
          '#element_validate' => [
            [$this, 'validateSimple'],
          ],
        ];
      }
      elseif ($entry_system == 'basic') {
        // A label showing the stock.
        $elements['stock']['stock_label'] = [
          '#type' => 'html_tag',
          '#tag' => 'strong',
          '#value' => $this->t('Stock level: @stock_level', ['@stock_level' => $level]),
        ];
        // An entry box for entring the a transaction amount.
        $elements['stock']['adjustment'] = [
          '#title' => $this->t('Transaction'),
          '#description' => $this->t('Valid options [number], +[number], -[number]. [number] for a new stock level, +[number] to add stock -[number] to remove stock. e.g. "5" we have 5 in stock, "+2" add 2 to stock or "-1" remove 1 from stock.'),
          '#type' => 'textfield',
          '#default_value' => '',
          '#size' => 7,
          '#maxlength' => 7,
          '#element_validate' => [
            array($this, 'validateBasic'),
          ],
        ];
      }
      elseif ($entry_system == 'transactions') {
        // A label showing the stock.
        $elements['stock']['stock_label'] = [
          '#type' => 'html_tag',
          '#tag' => 'strong',
          '#value' => $this->t('Stock level: @stock_level', ['@stock_level' => $level]),
        ];

        $url = Url::fromRoute('stock_ui.stock_transactions2', [])->toString() . '?commerce_product_v_id=' . $entity->id();
        $elements['stock']['stock_transactions_label'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('Please use the   <a href=":transactions_page" target="_blank">New transaction</a>  page to make changes.', [':transactions_page' => $url]),
        ];

        // Add a transaction note if enabled.
        if ($this->getSetting('transaction_note') && ($entry_system != 'transactions')) {
          $elements['stock']['stock_transaction_note'] = [
            '#title' => $this->t('Transaction note'),
            '#description' => $this->t('Type in a note about this transaction.'),
            '#type' => 'textfield',
            '#default_value' => '',
            '#size' => 20,
          ];
        }
      }
    }

    return $elements;
  }

  /**
   * Checks if a key exists somewhere in the given array.
   */
  private function multiKeyExists(array $arr, $key) {
    if (array_key_exists($key, $arr)) {
      return $arr[$key];
    }
    foreach ($arr as $element) {
      if (is_array($element)) {
        if ($found_element = $this->multiKeyExists($element, $key)) {
          return $found_element;
        }
      }
    }
    return FALSE;
  }

  /**
   * Save the Entity ID for stock update.
   *
   * This is a hack: As I don't know to get the relevent entity in the element
   * submit for the stock value field. We will store the ID.
   *
   * @todo: This is not go live ready code,
   */
  public function validateSimpleId($element, FormStateInterface $form_state) {
    $variation_id = $element['#value'];
    $commerce_stock_widget_values = &drupal_static('commerce_stock_widget_values', []);
    $commerce_stock_widget_values['variation_id'] = $variation_id;
  }

  /**
   * Simple stock form - Used to update the stock level.
   *
   * @todo: This is not go live ready code,
   */
  public function validateSimple($element, FormStateInterface $form_state) {

    if (!is_numeric($element['#value'])) {
      $form_state->setError($element, $this->t('Stock must be a number.'));
      return;
    }
    $values = $form_state->getValues();
    // Make sure we got variations.
    if (!isset($values['variations'])) {
      return;
    }

    $commerce_stock_widget_values = &drupal_static('commerce_stock_widget_values', []);

    // Get $variation_id using a hack (no live ready).
    $variation_id = $commerce_stock_widget_values['variation_id']['inline_entity_form']['entities'];

    // Init variable in case we can't find it.
    $transaction_note = FALSE;

    // Find the entity deap inside .
    $entities = $values['variations']['form']['inline_entity_form']['entities'];
    foreach ($entities as $entity) {
      $tmp_id = $this->multiKeyExists($entity, 'stocked_variation_id');
      if ($tmp_id == $variation_id) {
        $transaction_note = $this->multiKeyExists($entity, 'stock_transaction_note');
      }
    }

    $commerce_stock_widget_values['update_type'] = 'simple';
    $commerce_stock_widget_values['variation_id'] = $variation_id;
    $commerce_stock_widget_values['stock_level'] = $element['#value'];
    // Do we have a note.
    $commerce_stock_widget_values['transaction_note'] = $transaction_note;
    // Mark as need updating.
    $commerce_stock_widget_values['update'] = TRUE;
  }

  /**
   * Validates a basic stock field widget form.
   */
  public function validateBasic($element, FormStateInterface $form_state) {
    // @to do.
    return TRUE;
  }

  /**
   * Submits the form.
   */
  public static function closeForm($form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    drupal_set_message('updated STOCK');
  }

  /**
   * Submits the form.
   */
  public function submitAll(array &$form, FormStateInterface $form_state) {
    drupal_set_message('updated STOCK!!');
  }

}
