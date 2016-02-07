<?php
namespace Drupal\legal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Component\Utility\SafeMarkup;
use \Drupal\Core\Session\AccountInterface;
use \Drupal\Core\Access\AccessResult;

class LegalLanguageSettings extends FormBase {

  public function getFormId() {
    return 'legal_language_settings';
  }

  /**
   * Languages administration form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $latest_header = array(t('Language'), t('Version'), t('Revision'));
      $latest_rows   = $this->legal_versions_latest_get();
      $rows          = array();

      foreach ($latest_rows as $language_name => $language) {
        $row    = array();
        $row[]  = SafeMarkup::checkPlain($language_name);
        $row[]  = empty($language['version']) ? '-' : $language['version'];
        $row[]  = empty($language['revision']) ? '-' : $language['revision'];
        $rows[] = $row;
      }

      $form['latest'] = array(
        '#type'  => 'details',
        '#title' => t('Latest Version'),
      );

      $form['latest']['#value'] = array('#type' => 'table', '#header' => $latest_header, '#rows' => $rows);

      return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Get latest version for each language.
   */
  function legal_versions_latest_get($language = NULL) {
    $conditions      = array();
    $current_version = db_select('legal_conditions', 'lc')
      ->fields('lc', array('version'))
      ->orderBy('version', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchField();

    // get latest version for each language
    if (empty($language)) {
      $languages =  \Drupal::languageManager()->getLanguages();
      foreach ($languages as $language_id => $language) {
        $result = db_select('legal_conditions', 'lc')
          ->fields('lc')
          ->condition('version', $current_version)
          ->condition('language', $language_id)
          ->orderBy('revision', 'DESC')
          ->range(0, 1)
          ->execute()
          ->fetchAllAssoc('tc_id');
        $row    = count($result) ? (object)array_shift($result) : FALSE;

        $conditions[$language->getName()] = $this->legal_versions_latest_get_data($row);
      }

    } // get latest version for specific language
    else {
      $result = db_select('legal_conditions', 'lc')
        ->fields('lc')
        ->condition('language', $language)
        ->groupBy('language')
        ->orderBy('version', 'DESC')
        ->range(0, 1)
        ->execute()
        ->fetchAllAssoc('tc_id');
      $row    = count($result) ? (object)array_shift($result) : FALSE;

      $conditions[$language] = $this->legal_versions_latest_get_data($row);
    }

    return $conditions;
  }

  function legal_versions_latest_get_data($data) {
    $row['revision']   = isset($data->revision) ? $data->revision : '';
    $row['language']   = isset($data->language) ? $data->language : '';
    $row['conditions'] = isset($data->conditions) ? $data->conditions : '';
    $row['date']       = isset($data->date) ? $data->date : '';
    $row['extras']     = isset($data->extras) ? $data->extras : '';
    $row['changes']    = isset($data->changes) ? $data->changes : '';

    return $row;
  }

  /**
   * Access control callback.
   * Check that Locale module is enabled and user has access permission.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access(AccountInterface $account) {
    // Check permissions and combine that with any custom access checking needed. Pass forward
    // parameters from the route and/or request as needed.

      if (!\Drupal::moduleHandler()->moduleExists('locale')) {
        return AccessResult::forbidden();
      }

    /*  if (!\Drupal::currentUser()->hasPermission($perm)) {
        return FALSE;
      }*/

    return AccessResult::allowed();
  }


}