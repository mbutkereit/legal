<?php
namespace Drupal\legal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Require registered users to accept new T&C.
 */
class LegalLogin extends FormBase {
  /**
   * The account the shortcut set is for.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'legal_login';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state,UserInterface $user = NULL,$second = null) {
    $this->user = $user;

    if($user->isAnonymous()){
      throw new NotFoundHttpException();
    }

    $uid = $user->get('uid')->getString();
    $language = \Drupal::languageManager()->getCurrentLanguage();

      // get last accepted version for this account
      $legal_account = legal_get_accept($uid);
      // if no version has been accepted yet, get version with current language revision
      if (empty($legal_account['version'])) {
        $conditions = legal_get_conditions($language->getName());
        // no conditions set yet
        if (empty($conditions['conditions'])) return;
      }
      else { // get version / revision of last accepted language

        $conditions = legal_get_conditions($legal_account['language']);
        // no conditions set yet
        if (empty($conditions['conditions'])) return;
        // Check latest version of T&C has been accepted.
        $accepted = legal_version_check($uid, $conditions['version'], $conditions['revision'], $legal_account);

        if ($accepted) {
          return;
        }
      }

      $form = legal_display_fields($conditions);

      $form['uid'] = array(
        '#type'  => 'value',
        '#value' => $uid,
      );

      $form['id_hash'] = array(
        '#type'  => 'value',
        '#value' => $second,
      );

      $form['tc_id'] = array(
        '#type'  => 'value',
        '#value' => $conditions['tc_id'],
      );

      $form['version'] = array(
        '#type'  => 'value',
        '#value' => $conditions['version'],
      );

      $form['revision'] = array(
        '#type'  => 'value',
        '#value' => $conditions['revision'],
      );

      $form['language'] = array(
        '#type'  => 'value',
        '#value' => $conditions['language'],
      );

      $form = legal_display_changes($form, $uid);

      $form['save'] = array(
        '#type'  => 'submit',
        '#value' => t('Confirm'),
      );
      return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
     $values = $form_state->getValues();
       $user = $this->user;
        $uid = $user->get('uid')->getString();
      $redirect = '/user/' . $user->get('uid')->getString();

      if (!empty($_GET['destination'])) {
        $redirect = $_GET['destination'];
      }
      $form_state->setRedirectUrl(Url::fromUserInput($redirect));
      legal_save_accept($values['version'], $values['revision'], $values['language'], $uid);
      $this->logger('legal')->notice('%name accepted T&C version %tc_id.', array('%name' => $user->get('name')->getString(), '%tc_id' => $values['tc_id']));

      // Update the user table timestamp noting user has logged in.
      db_update('users_field_data')
        ->fields(array('login' => time()))
        ->condition('uid', $uid)
        ->execute();

    // User has new permissions, so we clear their menu cache.
     \Drupal::cache('menu')->delete($uid);

    \Drupal::service('session')->set('uid', $user->id());
    \Drupal::moduleHandler()->invokeAll('user_login', array($user));
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $result = db_select('users', 'u');
    $result->join('users_field_data','ufd','u.uid = ufd.uid');
    $result->fields('ufd');
    $result->fields('u');
    $result->condition('u.uid', $form_state->getValue('uid'));
      $result->range(0, 1);
        $result =$result->execute()
      ->fetchAllAssoc('uid');
    $account = array_pop($result);
    $id_hash = md5($account->name . $account->pass . $account->login);
    if ($id_hash != $form_state->getValue('id_hash')) {
      $form_state->setErrorByName('legal_accept',$this->t('User ID cannot be identified.'));
     // drupal_goto(); todo fix
    }
  }

}