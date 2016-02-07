<?php
namespace Drupal\legal\Controller;
use \Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;

class LegalController extends ControllerBase {
  public function legalPageAction() {
    $language = \Drupal::languageManager()->getCurrentLanguage();
    $conditions = legal_get_conditions($language->getName());
    $output = '';

    switch (\Drupal::config('legal.settings')->get('legal_display')) {
      case 0: // Scroll Box.
        $output = nl2br(strip_tags($conditions['conditions']));
        break;
      case 1: // CSS Scroll Box with HTML.
      case 2: // HTML.
      case 3: // Page Link.
        $output = Xss::filterAdmin($conditions['conditions']);
        break;
    }
    $build = array(
      '#type' => 'markup',
      '#markup' => $output,
    );

    return $build;
  }
}