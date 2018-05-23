<?php

namespace Drupal\short_url\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a short_url entity.
 *
 * @ingroup short_url
 */
class ShortUrlDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete entity for url %url?', ['%url' => $this->entity->get('original_url')->value]);
  }

  /**
   * {@inheritdoc}
   *
   * If the delete command is canceled, return to the short_url list.
   */
  public function getCancelUrl() {
    return new Url('entity.short_url.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * Delete the entity and log the event. logger() replaces the watchdog.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->delete();

    $this->logger('short_url')->notice('@type: deleted %url.',
      [
        '@type' => $this->entity->bundle(),
        '%url' => $this->entity->get('original_url')->value,
      ]);
    $form_state->setRedirect('entity.short_url.collection');
  }

}
