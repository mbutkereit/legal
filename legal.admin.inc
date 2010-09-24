<?php
// $Id$

/**
 * @file
 * Administration functions for the Legal module.
 */

function legal_administration() {

    $conditions = legal_get_conditions();
    $form = legal_display_fields($conditions);
    
    $form['conditions'] = array(
      '#type' => 'textarea',
      '#title' => t('Terms & Conditions'),
      '#default_value' => $conditions['conditions'],
      '#description' => t('Your Terms & Conditions'),
      '#required' => TRUE,
    ); 
    
    // overide accept checbox requirement on preview 
    $form['legal']['legal_accept']['#required'] = FALSE;
    
    // overide display setting
    $form['display'] = array(
      '#type' => 'radios',
      '#title' => t('Display Style'),
      '#default_value' => variable_get('legal_display', '0'),
      '#options' => array(t('Scroll Box'), t('Scroll Box (CSS)'), t('HTML Text'), t('Page Link')),
      '#description' => t('How terms & conditions should be displayed to users.'),
      '#required' => TRUE,
    );
    
    // additional checkboxes
    $form['extras'] = array(
      '#type' => 'fieldset',
      '#title' => t('Additional Checkboxes'),
      '#description' => t('Each field will be shown as a checkbox which the user must tick to register.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );
  
    $extras_count = count($conditions['extras']);
    if ($extras_count < 5) $extras_count = 5;
    
    for ($counter = 1; $counter <= $extras_count; $counter++) {
    
      $form['extras']['extras-' . $counter] = array(
        '#type' => 'textfield',
        '#title' => t('Label'),
        '#default_value' => $conditions['extras']['extras-' . $counter],
      );
      
      // overide extra checkboxes  
      if (!empty($conditions['extras']['extras-' . $counter])) {
        $form['legal']['extras-' . $counter] = array(
          '#type' => 'checkbox',
          '#title' => filter_xss_admin($conditions['extras']['extras-' . $counter]),
          '#default_value' => 0,
          '#weight' => 2,
          '#required' => FALSE,   
        ); 
      } 
    }
    
    // notes about changes to T&C
    $form['changes'] = array(
      '#type' => 'fieldset',
      '#title' => t('Explain Changes'),
      '#description' => t('Explain what changes were made to the T&C since the last version. This will only be shown to users who accepted a previous version. Each line will automatically be shown as a bullet point.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    
    $form['changes']['changes'] = array(
        '#type' => 'textarea',
        '#title' => t('Changes'),
    );
    
    $form['#after_build'] = array('legal_preview');
    
    $form['preview'] = array(
      '#type' => 'button',
      '#value' => t('Preview'),
    );
    
    $form['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
    
    return $form;
}

function legal_preview($form, $form_values) {

    switch ($form['display']['#value']) {
    
        case 1: // scroll box (CSS)
        case 2: // HTML
            $form['legal']['conditions'] = array(
              '#value' => filter_xss_admin($form['conditions']['#value']),
            );
            $form['legal']['legal_accept']['#title'] = t('<strong>Accept</strong> Terms & Conditions of Use');
            break;
        case 3: // Page Link
            $form['legal']['conditions'] = array(
              '#value' => ' ',
            );
            $form['legal']['legal_accept']['#title'] = t('<strong>Accept</strong> !terms of Use', array('!terms' => l(t('Terms & Conditions'), 'legal')));
            break;  
        default: // scroll box (HTML)
            $form['legal']['conditions'] = array(
              '#type' => 'textarea',
              '#title' => t('Terms & Conditions'),
              '#value' => $form['conditions']['#value'],
              '#parents' => array('legal'),
              '#rows' => 10,
              '#attributes' => array('readonly' => 'readonly'),
            );
            $form['legal']['legal_accept']['#title'] = t('<strong>Accept</strong> Terms & Conditions of Use');
    }

    // overide additional checkboxes in preview
    if (!empty($form_values['extras'])) {
   
        while (list ($key, $label) = each ($form_values['extras'])) {
            
            if (empty($label)) {
                unset($form['legal'][$key]);
            } else {
                $form['legal'][$key]['#title'] = filter_xss_admin($label);
            }

        }
        
    }   

    return $form;
}

function legal_administration_validate($form, &$form_state) {

  if ( empty($form_state['values']['conditions']) ) form_set_error('conditions', t('Terms & Conditions must be entered.'));
  return;
}

function legal_administration_submit($form, &$form_state) {

  $values = $form_state['values'];    
  if ($form_state['clicked_button']['#value'] == t('Preview')) return;

  if ( variable_get('legal_display', '0') !=  $form_state['values']['display'] ) {
      variable_set('legal_display', $form_state['values']['display']);
      drupal_set_message(t('Display setting has been saved.'));
  }

  // If new conditions are different from current permisions - enter in database
  if ( legal_conditions_updated($form) ) {
      db_query("INSERT INTO {legal_conditions} (tc_id, conditions, date, extras, changes) VALUES (NULL, '%s', %d, '%s', '%s')", $values['conditions'], time(), serialize($values['extras']), $values['changes']);
      drupal_set_message (t('Terms & Conditions have been saved.'));
  }
  
  cache_clear_all();
  return;  
}

function theme_legal_administration($form) {

    if (empty($form['current_id']['#value'])) {
        $output = '<p><strong>' .t('Terms & Conditions will not be shown to users, as no T&C have been saved.') . '</strong></p>';
    } else {
        $output = '<h4>' . t('Current Version') . '</h4><p><strong>' . t('Version ID:') . '</strong> ' . $form['current_id']['#value'] . '<br /><strong>' . t('Last saved:') . '</strong> ' . date("l jS \of F Y h:i:s A", $form['current_date']['#value']) . '</p>';
    }
    
    // preview
    if (empty($form['legal']['conditions']['#value'])) {
        drupal_render($form['legal']);
    } else {
        
        $output .= '<div id="preview">';
        $output .= '<h3>'. t('Preview') .'</h3>';
        $form = theme('legal_display', $form);
        $output .= drupal_render($form['legal']);
        $output .= '</div>';
    }

    $output .= drupal_render($form);
    
    return $output;
}
