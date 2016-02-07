<?php

namespace Drupal\legal\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Xss;

class LegalAdminSettingsForm extends FormBase {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(ModuleHandlerInterface $module_handler,
                              LanguageManagerInterface $language_manager,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache_render,
                              Connection $database) {
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->cacheRender = $cache_render;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
      return 'legal_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('cache.render'),
      $container->get('database')
      );
  }
  
  /**
   * Module settings form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $form       = array();
      $conditions = legal_get_conditions();

      if ($this->moduleHandler->moduleExists('locale')) {
        $languages        = $this->languageManager->getLanguages();
        $language_default = $this->languageManager->getDefaultLanguage();
        $language         = $language_default->getName();
        $version_options  = array('version' => t('All users (new version)'), 'revision' => t('Language specific users (a revision)'));
        $version_handling = 'version';
      }
      else {
        $languages        = array('en' => t('English'));
        $language         = 'en';
        $version_handling = 'version';
      }

      $form = array_merge($form, legal_display_fields($conditions));

      $form['conditions'] = array(
        '#type'          => 'textarea',
        '#title'         => t('Terms & Conditions'),
        '#default_value' => $conditions['conditions'],
        '#description'   => t('Your Terms & Conditions'),
        '#required'      => TRUE,
      );

      // Set this here or array will flatten out and override real values.
      $form['legal']['#tree'] = TRUE;

      // Overide accept checbox requirement on preview.
      $form['legal']['legal_accept']['#required'] = FALSE;

      // Overide display setting.
      $form['display'] = array(
        '#type'          => 'radios',
        '#title'         => t('Display Style'),
        '#default_value' => $this->configFactory->get('legal.settings')->get('legal_display'),
        '#options'       => array(t('Scroll Box'), t('Scroll Box (CSS)'), t('HTML Text'), t('Page Link')),
        '#description'   => t('How terms & conditions should be displayed to users.'),
        '#required'      => TRUE,
      );

      // Only display options if there's more than one language available.
      if (count($languages) > 1) {
        // Language and version handling options.
        $form['language'] = array(
          '#type'        => 'details',
          '#title'       => t('Language'),
        );

        $form['language']['language'] = array(
          '#type'          => 'select',
          '#title'         => t('Language'),
          '#options'       => $languages,
          '#default_value' => $language,
        );

        $form['language']['version_handling'] = array(
          '#type'          => 'select',
          '#title'         => t('Ask To Re-accept'),
          '#description'   => t('<strong>All users</strong>: all users will be asked to accept the new version of the T&C, including users who accepted a previous version.<br />
                           <strong>Language specific</strong>: only new users, and users who accepted the T&C in the same language as this new revision will be asked to re-accept.'),
          '#options'       => $version_options,
          '#default_value' => $version_handling,
        );
      }
      else {
        $form['language']['language']         = array('#type' => 'value', '#value' => $language);
        $form['language']['version_handling'] = array('#type' => 'value', '#value' => $version_handling);

      }

      // Additional checkboxes.
      $form['extras'] = array(
        '#type'        => 'details',
        '#title'       => t('Additional Checkboxes'),
        '#description' => t('Each field will be shown as a checkbox which the user must tick to register.'),
        '#open' => false,
        '#tree'        => TRUE,
      );

      $extras_count = ((count($conditions['extras']) < 10) ? 10 : count($conditions['extras']));

      for ($counter = 1; $counter <= $extras_count; $counter++) {
        $extra = isset($conditions['extras']['extras-' . $counter]) ? $conditions['extras']['extras-' . $counter] : '';

        $form['extras']['extras-' . $counter] = array(
          '#type'          => 'textarea',
          '#title'         => t('Label'),
          '#default_value' => $extra,
        );

        // Overide extra checkboxes.
        if (!empty($conditions['extras']['extras-' . $counter])) {
          $form['legal']['extras-' . $counter] = array(
            '#type'          => 'checkbox',
            '#title'         => Xss::filterAdmin($extra),
            '#default_value' => 0,
            '#weight'        => 2,
            '#required'      => FALSE,
          );
        }
      }

      // Notes about changes to T&C.
      $form['changes'] = array(
        '#type'        => 'details',
        '#title'       => t('Explain Changes'),
        '#description' => t('Explain what changes were made to the T&C since the last version. This will only be shown to users who accepted a previous version. Each line will automatically be shown as a bullet point.'),
      );

      $form['changes']['changes'] = array(
        '#type'  => 'textarea',
        '#title' => t('Changes'),
      );

      $form['#after_build'] = array('legal_preview');

      $form['preview'] = array(
        '#type'  => 'button',
        '#value' => t('Preview'),
      );

      $form['save'] = array(
        '#type'  => 'submit',
        '#value' => t('Save'),
      );

      return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
      $values = $form_state->getValues();
      // Preview request, don't save anything. TODO find the right
      if ($form_state->getTriggeringElement()['#value'] == t('Preview')) {
        return;
      }

      if ($this->configFactory->get('legal.settings')->get('legal_display') != $values['display']) {
        $this->configFactory->getEditable('legal.settings')
            ->set('legal_display', $values['display'])
            ->save();
        drupal_set_message(t('Display setting has been saved.'));
      }

      // If new conditions are different from current permisions, enter in database.
      if ($this->legal_conditions_updated($values)) {
        $version = legal_version($values['version_handling'], $values['language']);

        db_insert('legal_conditions')
          ->fields(array(
            'version'    => $version['version'],
            'revision'   => $version['revision'],
            'language'   => $values['language'],
            'conditions' => $values['conditions'],
            'date'       => time(),
            'extras'     => serialize($values['extras']),
            'changes'    => $values['changes'],
          ))
          ->execute();

        drupal_set_message(t('Terms & Conditions have been saved.'));
      }

      // Empty all cache.
      // @todo: is this necessary?
      $this->cacheRender->deleteAll();
    }

  /**
   * Check if T&Cs have been updated.
   */
 protected function legal_conditions_updated($new) {
    $previous_same_language = legal_get_conditions($new['language']);
    $previous               = legal_get_conditions();

    if (($previous_same_language['conditions'] != $new['conditions']) && ($previous['conditions'] != $new['conditions'])) {
      return TRUE;
    }

    $count = count($new['extras']);

    for ($counter = 1; $counter <= $count; $counter++) {
      $previous_same_language_extra = isset($previous_same_language['extras']['extras-' . $counter]) ? $previous_same_language['extras']['extras-' . $counter] : '';
      $previous_extra               = isset($previous['extras']['extras-' . $counter]) ? $previous['extras']['extras-' . $counter] : '';

      if (($previous_same_language_extra != $new['extras']['extras-' . $counter]) && ($previous_extra != $new['extras']['extras-' . $counter])) {
        return TRUE;
      }
    }

    return FALSE;
  }

}