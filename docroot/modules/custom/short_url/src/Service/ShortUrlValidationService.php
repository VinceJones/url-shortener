<?php

namespace Drupal\short_url\Service;


use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\short_url\Entity\ShortUrl;

class ShortUrlValidationService {

  /**
   * Return a clean string that we can check if is valid.
   *
   * @param FormStateInterface $form_state Form state of submitted entity form.
   *
   * @return string
   *   Properly escaped string.
   */
  public function cleanUrl(FormStateInterface $form_state) {
    return Html::escape(
      UrlHelper::stripDangerousProtocols(
        $form_state->getValue('original_url')[0]['value']
      )
    );
  }

  /**
   * Check if the string looks like a url.
   *
   * @param string $url Url to Validate.
   *
   * @return bool
   *   Whether the url validates.
   */
  public function isValidUrl($url) {

    if ($valid = filter_var($url, FILTER_VALIDATE_URL)) {
      return true;
    }

    return false;
  }

  /**
   * Validate the url by sending a curl request to check response code.
   *
   * @param string $url Url to Validate.
   *
   * @return bool
   *   Whether url returns valid response code.
   */
  public function hasValidResponseCode($url) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);
    $response_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Make sure the response code is not 4xx
    if ($response_status[0] != '4') {
      return true;
    }

    return false;
  }

  /**
   * Create a shortened url from a saved entity.
   *
   * @param ShortUrl $entity Entity to create short url from.
   *
   * @return bool|string
   */
  public function getShortenedUrl(ShortUrl &$entity) {

    $start_char = 0;
    $end_char = 4;
    $long_uuid = $entity->uuid();

    $short_uuid = substr($long_uuid, $start_char, $end_char);

    /*
     * Check if the short_uuid currently exists in the database, if it does
     *   then increase the length of the short uuid until we get a uuid that
     *   doesn't exist.
     */
    while (!empty($exists = $this->getShortUrlEntityByPath($short_uuid))) {
      $end_char++;
      $short_uuid = substr($long_uuid, $start_char, $end_char);
      $exists = $this->getShortUrlEntityByPath($short_uuid);
    }

    return $short_uuid;
  }

  /**
   * Search the database to find out if entity exists.
   *
   * @param $short_url $path Path to search by.
   *
   * @return array|int
   *   Entities or int determining whether the entity exists in the database.
   */
  public function getShortUrlEntityByPath($short_url) {
    return \Drupal::entityQuery('short_url')
      ->condition('short_url', $short_url)
      ->execute();
  }

}