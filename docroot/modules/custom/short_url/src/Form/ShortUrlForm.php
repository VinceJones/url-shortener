<?php

namespace Drupal\short_url\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the short_url entity edit forms.
 *
 * @ingroup short_url
 */
class ShortUrlForm extends ContentEntityForm {

  /**
   * ShortUrlValidationService.
   *
   * @var \Drupal\short_url\Service\ShortUrlValidationService
   */
  public $validation_service;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\short_url\Entity\ShortUrl */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    // Reset the form submit button text
    $form['actions']['submit']['#value'] = $this->t("Get Shortened Url");

    $form['langcode'] = [
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $this->validation_service = \Drupal::service('short_url.validation_service');

    if (empty($form_state->getValue('original_url'))) {
      $form_state->setErrorByName('original_url', 'Please set a url to shorten');
    }

    if (
      !isset($form_state->getValue('original_url')[0]) ||
      !isset($form_state->getValue('original_url')[0]['value'])
    ) {
      $form_state->setErrorByName('original_url', 'Please set a url to shorten');
    }

    $original_url = $this->validation_service->cleanUrl($form_state);

    if (!$this->validation_service->isValidUrl($original_url)) {
      $form_state->setErrorByName('original_url', 'Please provide a valid url');
    }

    if (!$this->validation_service->hasValidResponseCode($original_url)) {
      $form_state->setErrorByName('original_url', 'Please provide a valid url');
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $form_state->setRedirect('entity.short_url.collection');

    /** @var \Drupal\short_url\Entity\ShortUrl $entity */
    $entity = $this->getEntity();

    $entity->save();

    if (empty($entity->get('short_url')->value)) {
      $entity->set('short_url', $this->validation_service->getShortenedUrl($entity));
      $entity->set('hit_count', 0);
      $entity->save();
    }

  }
}
