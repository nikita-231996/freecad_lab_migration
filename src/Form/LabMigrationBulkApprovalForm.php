<?php

/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationBulkApprovalForm.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Database\Database;
use Drupal\Component\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;

class LabMigrationBulkApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_bulk_approval_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $options_first = $this->_bulk_list_of_labs();
    $options_two = $this->_ajax_bulk_get_experiment_list();
    $selected = !$form_state->getValue(['lab']) ? $form_state->getValue(['lab']) : key($options_first);
    $select_two = !$form_state->getValue(['lab_experiment_list']) ? $form_state->getValue([
      'lab_experiment_list'
      ]) : key($options_two);
    $form = [];
    $form['lab'] = [
      '#type' => 'select',
      '#title' => t('Title of the lab'),
      '#options' => $this->_bulk_list_of_labs(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => '::ajax_bulk_experiment_list_callback',
        'wrapper' => 'ajax_selected_lab'
        
        ],
      //'#suffix' => '<div id="ajax_selected_lab"></div><div id="ajax_selected_lab_pdf"></div>',
    ];
   
    $form['lab_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for Entire Lab'),
      '#options' => $this->_bulk_list_lab_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_lab_action" style="color:red;">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['update_exp'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax_selected_lab']
    ];
    $form['update_exp']['lab_experiment_list'] = [
      '#type' => 'select',
      '#title' => t('Title of the experiment'),
      '#options' => $this->_ajax_bulk_get_experiment_list($form_state->getValue('lab')),
      '#default_value' => !$form_state->getValue([
        'lab_experiment_list'
        ]) ? $form_state->getValue(['lab_experiment_list']) : '',
      '#ajax' => [
        'callback' => '::ajax_bulk_solution_list_callback',
        'wrapper' => 'ajax_download_experiment'
        ],
      '#prefix' => '<div id="ajax_selected_experiment">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['download_experiment_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax_download_experiments'],
    ];
    $form['download_experiment_wrapper']['download_experiment'] = [
        '#type' => 'item',
        '#markup' => Link::fromTextAndUrl('Download Experiment', Url::fromUri('internal:/lab_migration/download/experiment/' . $form_state->getValue('lab_experiment_list')))->toString()
    ];
    $form['download_experiment_wrapper']['solution_list'] = [
      '#type' => 'select',
        '#title' => t('Title of the solution'),
        '#options' => $this->_ajax_get_solution_list($form_state->getValue('lab_experiment_list')),
        '#ajax' => [
            'callback' => '::ajax_solution_files_callback',
            'wrapper'  => 'ajax_download_solution_file'
          ],
    ];
    $form['download_solution_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax_download_solution_file'],
    ];
    $form['download_solution_wrapper']['download_solution'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl('Download Solution', Url::fromUri('internal:/lab_migration/download/solution/' . $form_state->getValue('solution_list')))->toString()
    ];
    $form['lab_experiment_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for Entire Experiment'),
      '#options' =>$this->_bulk_list_experiment_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_lab_experiment_action" style="color:red;">',
      '#suffix' => '</div>',
      //'#states' => array('invisible' => array(':input[name="lab"]' => array('value' => 0),),),  
        '#states' => [
        'invisible' => [
          [
            [
              ':input[name="lab"]' => [
                'value' => 0
                ]
              ],
            'or',
            [':input[name="lab_actions"]' => ['value' => 0]],
          ]
          ]
        ],
    ];
    $form['lab_solution_list'] = [
      '#type' => 'select',
      '#title' => t('Solution'),
      '#options' => $this->_ajax_bulk_get_solution_list($select_two),
      '#default_value' => !$form_state->getValue([
        'lab_solution_list'
        ]) ? $form_state->getValue(['lab_solution_list']) : '',
      '#ajax' => [
        'callback' => '::ajax_bulk_solution_files_callback',
        'wrapper' => 'ajax_bulk_solution_files'
        ],
      '#prefix' => '<div id="ajax_selected_solution">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['lab_experiment_solution_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for solution'),
      '#options' => $this->_bulk_list_solution_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_lab_experiment_solution_action" style="color:red;">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ]; $form['download_solution_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax_download_solution_file'],
    ];
    $form['download_solution_wrapper']['download_solution'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl('Download Solution', Url::fromUri('internal:/lab_migration/download/solution/' . $form_state->getValue('solution_list')))->toString()
    ];
   
    $form['solution_files'] = [
      '#type' => 'item',
      // '#title' => t('List of solution_files'),
        '#markup' => '<div id="ajax_solution_files"></div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('If Dis-Approved please specify reason for Dis-Approval'),
      '#prefix' => '<div id= "message_submit">',
      '#states' => [
        'visible' => [
          [
            [
              ':input[name="lab_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [
              ':input[name="lab_experiment_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [
              ':input[name="lab_experiment_solution_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [':input[name="lab_actions"]' => ['value' => 4]],
          ]
          ],
        'required' => [
          [
            [':input[name="lab_actions"]' => ['value' => 3]],
            'or',
            [
              ':input[name="lab_experiment_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [
              ':input[name="lab_experiment_solution_actions"]' => [
                'value' => 3
                ]
              ],
            'or',
            [':input[name="lab_actions"]' => ['value' => 4]],
          ]
          ],
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    return $form;
  }
  
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $root_path = \Drupal::service("lab_migration_global")->lab_migration_path();
    if ($form_state->get(['clicked_button', '#value']) == 'Submit') {
      if ($form_state->getValue(['lab']))
        //lab_migration_del_lab_pdf($form_state['values']['lab']);
 {
        if (user/accountInterface('lab migration bulk manage code')) {
          $query = \Drupal::database()->select('lab_migration_proposal');
          $query->fields('lab_migration_proposal');
          $query->condition('id', $form_state->getValue(['lab']));
          $user_query = $query->execute();
          $user_info = $user_query->fetchObject();
          $user_data = loadMultiple($user_info->uid);
          if (($form_state->getValue(['lab_actions']) == 1) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
            /* approving entire lab */
            //   $experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $form_state['values']['lab']);
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('proposal_id', $form_state->getValue(['lab']));
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_list = '';
            while ($experiment_data = $experiment_q->fetchObject()) {
              //  \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = %d WHERE experiment_id = %d AND approval_status = 0", $user->uid, $experiment_data->id);
              \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = :approver_uid WHERE experiment_id = :experiment_id AND approval_status = 0", [
                ':approver_uid' => $user->uid,
                ':experiment_id' => $experiment_data->id,
              ]);
              $experiment_list .= '<p>' . $experiment_data->number . ') ' . $experiment_data->title . '<br> Description :  ' . $experiment_data->description . '<br>';
              $experiment_list .= ' ';
              $experiment_list .= '</p>';
            }
            \Drupal::messenger()->addmessage(t('Approved Entire Lab.'), 'status');
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been approved', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

Your all the uploaded solutions for the Lab with the below detail has been approved:

Title of Lab:' . $user_info->lab_title . '

List of experiments: ' . $experiment_list . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
          }
          elseif (($form_state->getValue(['lab_actions']) == 2) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
            /* pending review entire lab */
            //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $form_state['values']['lab']);
            $experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = :proposal_id", [
              ':proposal_id' => $form_state->getValue(['lab'])
              ]);
            while ($experiment_data = $experiment_q->fetchObject()) {
              //\Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE experiment_id = %d", $experiment_data->id);
              \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE experiment_id = :experiment_id", [
                ":experiment_id" => $experiment_data->id
                ]);
            }
            \Drupal::messenger()->addmessage(t('Pending Review Entire Lab.'), 'status');
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been marked as pending', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

Your all the uploaded solutions for the Lab with Title: ' . $user_info->lab_title . ' have been marked as pending to be reviewed.
 
You will be able to see the solutions after they have been approved by one of our reviewers.

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /* $email_subject = t('Your uploaded Lab Migration solutions have been marked as pending');
                $email_body = array(0 => t('Your all the uploaded solutions for the Lab have been marked as pending to be review. You will be able to see the solutions after they have been approved by one of our reviewers.'));*/
          }
          elseif (($form_state->getValue(['lab_actions']) == 3) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {

            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              \Drupal::messenger()->addmessage("Please mention the reason for disapproval. Minimum 30 character required", 'error');
              return;
            }
            if (!user/accountInterface('lab migration bulk delete code')) {
              \Drupal::messenger()->addmessage(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Lab.'), 'error');
              return;
            }
            if (lab_migration_delete_lab($form_state->getValue(['lab']))) {
              \Drupal::messenger()->addmessage(t('Dis-Approved and Deleted Entire Lab.'), 'status');
            }
            else {
              \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Entire Lab.'), 'error');
            }
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been marked as dis-approved', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

Your all the uploaded solutions for the whole Lab with Title: ' . $user_info->lab_title . ' have been marked as dis-approved.

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /* $email_subject = t('Your uploaded Lab Migration solutions have been marked as dis-approved');
                $email_body = array(0 =>t('Your all the uploaded solutions for the whole Lab have been marked as dis-approved.
                
                Reason for dis-approval:
                
                ' . $form_state['values']['message']));*/
          }
          elseif (($form_state->getValue(['lab_actions']) == 4) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              \Drupal::messenger()->addmessage("Please mention the reason for disapproval/deletion. Minimum 30 character required", 'error');
              return;
            }
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('proposal_id', $form_state->getValue(['lab']));
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_list = '';
            while ($experiment_data = $experiment_q->fetchObject()) {
              $experiment_list .= '<p>' . $experiment_data->number . ') ' . $experiment_data->title . '<br> Description:  ' . $experiment_data->description . '<br>';
              $experiment_list .= ' ';
              $experiment_list .= '</p>';
            }
            if (!user/accountInterface('lab migration bulk delete code')) {
              \Drupal::messenger()->addmessage(t('You do not have permission to Bulk Delete Entire Lab Including Proposal.'), 'error');
              return;
            }
            /* check if dependency files are present */
            $dep_q = \Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE proposal_id = :proposal_id", [
              ":proposal_id" => $form_state->getValue(['lab'])
              ]);
            if ($dep_data = $dep_q->fetchObject()) {
              \Drupal::messenger()->addmessage(t("Cannot delete lab since it has dependency files that can be used by others. First delete the dependency files before deleting the lab."), 'error');
              return;
            }
            if (lab_migration_delete_lab($form_state->getValue(['lab']))) {
              \Drupal::messenger()->addmessage(t('Dis-Approved and Deleted Entire Lab solutions.'), 'status');
              $query = \Drupal::database()->select('lab_migration_proposal');
              $query->fields('lab_migration_proposal');
              $query->condition('id', $form_state->getValue(['lab']));
              $proposal_q = $query->execute()->fetchObject();
              $query = \Drupal::database()->select('lab_migration_experiment');
              $query->fields('lab_migration_experiment');
              $query->condition('proposal_id', $form_state->getValue(['lab']));
              $experiment_q = $query->execute();
              $experiment_data = $experiment_q->fetchObject();
              $exp_path = $root_path . $proposal_q->directory_name . '/EXP' . $experiment_data->number;
              $dir_path = $root_path . $proposal_q->directory_name;
              if (is_dir($dir_path)) {
                rmdir($exp_path);
                $res = rmdir($dir_path);
                if (!$res) {
                  \Drupal::messenger()->addmessage(t("Cannot delete Lab directory: " . $dir_path . ". Please contact administrator."), 'error');
                  return;
                }
              }
              else {
                \Drupal::messenger()->addmessage(t("Lab directory not present: " . $dir_path . ". Skipping deleting lab directory."), 'status');
              }
              /* deleting full proposal */
              //$proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $form_state['values']['lab']);
              $proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = :id", [
                ":id" => $form_state->getValue(['lab'])
                ]);
              $proposal_data = $proposal_q->fetchObject();
              $proposal_id = $proposal_data->id;
              \Drupal::database()->query("DELETE FROM {lab_migration_experiment} WHERE proposal_id = :proposal_id", [
                ":proposal_id" => $proposal_id
                ]);
              \Drupal::database()->query("DELETE FROM {lab_migration_proposal} WHERE id = :id", [
                ":id" => $proposal_id
                ]);
              \Drupal::messenger()->addmessage(t('Deleted Lab Proposal.'), 'status');
              /* email */
              $email_subject = t('[!site_name] Your uploaded Lab Migration solutions including the Lab proposal have been deleted', [
                '!site_name' => $config->get('site_name', '')
                ]);
              $email_body = [
                0 => t('

Dear ' . $proposal_data->name . ',

We regret to inform you that all the uploaded Experiments of your Lab with following details have been deleted permanently.

Title of Lab:' . $user_info->lab_title . '

List of experiments: ' . $experiment_list . '

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Best Wishes,

!site_name Team
FOSSEE, IIT Bombay', [
                  '!site_name' => $config->get('site_name', ''),
                  '!user_name' => $user_data->name,
                ])
                ];
              /* email */
              /*  $email_subject = t('Your uploaded Lab Migration solutions including the Lab proposal have been deleted');
                    $email_body = array(0 =>t('Your all the uploaded solutions including the Lab proposal have been deleted permanently.'));*/
            }
            else {
              \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Entire Lab.'), 'error');
            }
          }
          elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 1) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
            \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = :approver_uid WHERE experiment_id = :experiment_id AND approval_status = 0", [
              ":approver_uid" => $user->uid,
              ":experiment_id" => $form_state->getValue(['lab_experiment_list']),
            ]);
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('id', $form_state->getValue(['lab_experiment_list']));
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_value = $experiment_q->fetchObject();
            $query = \Drupal::database()->select('lab_migration_solution');
            $query->fields('lab_migration_solution');
            $query->condition('experiment_id', $form_state->getValue(['lab_experiment_list']));
            $query->orderBy('code_number', 'ASC');
            $solution_q = $query->execute();
            $solution_value = $solution_q->fetchObject();
            \Drupal::messenger()->addmessage(t('Approved Entire Experiment.'), 'status');
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solution have been approved', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

Your Experiment for R Lab Migration with the following details is approved.

Experiment name: ' . $experiment_value->title . '
Caption: ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /* $email_subject = t('Your uploaded Lab Migration solutions have been approved');
                $email_body = array(0 =>t('Your all the uploaded solutions for the experiment have been approved.'));*/
          }
          elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 2) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
            \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE experiment_id = :experiment_id", [
              ":experiment_id" => $form_state->getValue(['lab_experiment_list'])
              ]);
            \Drupal::messenger()->addmessage(t('Entire Experiment marked as Pending Review.'), 'status');
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('id', $form_state->getValue(['lab_experiment_list']));
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_value = $experiment_q->fetchObject();
            $query = \Drupal::database()->select('lab_migration_solution');
            $query->fields('lab_migration_solution');
            $query->condition('experiment_id', $form_state->getValue(['lab_experiment_list']));
            $query->orderBy('code_number', 'ASC');
            $solution_q = $query->execute();
            $solution_value = $solution_q->fetchObject();
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solution have been marked as pending', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

Your all the uploaded solutions for the experiment have been marked as pending to be reviewed.

Experiment name: ' . $experiment_value->title . '
Caption: ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /*$email_subject = t('Your uploaded Lab Migration solutions have been marked as pending');
                $email_body = array(0 =>t('Your all the uploaded solutions for the experiment have been marked as pending to be review.'));*/
          }
          elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 3) && ($form_state->getValue(['lab_experiment_solution_actions']) == 0)) {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              \Drupal::messenger()->addmessage("Please mention the reason for disapproval. Minimum 30 character required", 'error');
              return;
            }
            if (!user/accountInterface('lab migration bulk delete code')) {
              \Drupal::messenger()->addmessage(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Experiment.'), 'error');
              return;
            }
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('id', $form_state->getValue(['lab_experiment_list']));
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_value = $experiment_q->fetchObject();
            $query = \Drupal::database()->select('lab_migration_solution');
            $query->fields('lab_migration_solution');
            $query->condition('experiment_id', $form_state->getValue(['lab_experiment_list']));
            $query->orderBy('code_number', 'ASC');
            $solution_q = $query->execute();
            $solution_value = $solution_q->fetchObject();
            if (lab_migration_delete_experiment($form_state->getValue(['lab_experiment_list']))) {
              \Drupal::messenger()->addmessage(t('Dis-Approved and Deleted Entire Experiment.'), 'status');
            }
            else {
              \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Entire Experiment.'), 'error');
            }
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solutions have been marked as dis-approved', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

We regret to inform you that your experiment with the following details under R Lab Migration is disapproved and has been deleted.

Experiment name: ' . $experiment_value->title . '
Caption: ' . $solution_value->caption . '

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Please resubmit the modified solution.

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /*$email_subject = t('Your uploaded Lab Migration solutions have been marked as dis-approved');
                $email_body = array(0 => t('Your uploaded solutions for the entire experiment have been marked as dis-approved.
                
                Reason for dis-approval:
                
                ' . $form_state['values']['message']));*/
          }
          elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 1)) {
            $query = \Drupal::database()->select('lab_migration_solution');
            $query->fields('lab_migration_solution');
            $query->condition('id', $form_state->getValue(['lab_solution_list']));
            $query->orderBy('code_number', 'ASC');
            $solution_q = $query->execute();
            $solution_value = $solution_q->fetchObject();
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('id', $solution_value->experiment_id);
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_value = $experiment_q->fetchObject();
            \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 1, approver_uid = :approver_uid WHERE id = :id", [
              ":approver_uid" => $user->uid,
              ":id" => $form_state->getValue(['lab_solution_list']),
            ]);
            \Drupal::messenger()->addmessage(t('Solution approved.'), 'status');
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solution has been approved', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

Your Experiment for R Lab Migration with the following details is approved.

Experiment name: ' . $experiment_value->title . '
Caption: ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /* $email_subject = t('Your uploaded Lab Migration solution has been approved');
                $email_body = array(0 =>t('Your uploaded solution has been approved.'));*/
          }
          elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 2)) {
            $query = \Drupal::database()->select('lab_migration_solution');
            $query->fields('lab_migration_solution');
            $query->condition('id', $form_state->getValue(['lab_solution_list']));
            $query->orderBy('code_number', 'ASC');
            $solution_q = $query->execute();
            $solution_value = $solution_q->fetchObject();
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('id', $solution_value->experiment_id);
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_value = $experiment_q->fetchObject();
            \Drupal::database()->query("UPDATE {lab_migration_solution} SET approval_status = 0 WHERE id = :id", [
              ":id" => $form_state->getValue(['lab_solution_list'])
              ]);
            \Drupal::messenger()->addmessage(t('Solution marked as Pending Review.'), 'status');
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solution has been marked as pending', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

Your all the uploaded solutions for the experiment have been marked as pending to be reviewed.

Experiment name: ' . $experiment_value->title . '
Caption: ' . $solution_value->caption . '

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /*$email_subject = t('Your uploaded Lab Migration solution has been marked as pending');
                $email_body = array(0 =>t('Your uploaded solution has been marked as pending to be review.'));*/
          }
          elseif (($form_state->getValue(['lab_actions']) == 0) && ($form_state->getValue(['lab_experiment_actions']) == 0) && ($form_state->getValue(['lab_experiment_solution_actions']) == 3)) {
            $query = \Drupal::database()->select('lab_migration_solution');
            $query->fields('lab_migration_solution');
            $query->condition('id', $form_state->getValue(['lab_solution_list']));
            $query->orderBy('code_number', 'ASC');
            $solution_q = $query->execute();
            $solution_value = $solution_q->fetchObject();
            $query = \Drupal::database()->select('lab_migration_experiment');
            $query->fields('lab_migration_experiment');
            $query->condition('id', $solution_value->experiment_id);
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_value = $experiment_q->fetchObject();
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              \Drupal::messenger()->addmessage("Please mention the reason for disapproval. Minimum 30 character required", 'error');
              return;
            }
            if (lab_migration_delete_solution($form_state->getValue(['lab_solution_list']))) {
              \Drupal::messenger()->addmessage(t('Solution Dis-Approved and Deleted.'), 'status');
            }
            else {
              \Drupal::messenger()->addmessage(t('Error Dis-Approving and Deleting Solution.'), 'error');
            }
            /* email */
            $email_subject = t('[!site_name] Your uploaded Lab Migration solution has been marked as dis-approved', [
              '!site_name' => $config->get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear !user_name,

We regret to inform you that your experiment with the following details under R Lab Migration is disapproved and has been deleted.

Experiment name: ' . $experiment_value->title . '
Caption: ' . $solution_value->caption . '

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Please resubmit the modified solution.

Best Wishes,

!site_name Team,
FOSSEE,IIT Bombay', [
                '!site_name' => $config->get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /* email */
            /* $email_subject = t('Your uploaded Lab Migration solution has been marked as dis-approved');
                $email_body = array(0 =>t('Your uploaded solution has been marked as dis-approved.
                
                Reason for dis-approval:
                
                ' . $form_state['values']['message']));*/
          }
          else {
            \Drupal::messenger()->addmessage(t('Please select only one action at a time'), 'error');
            return;
          }
          /** sending email when everything done **/
        if ($email_subject) {
            $email_to = $user_data->mail;
            $from = $config->get('lab_migration_from_email', '');
            $bcc = $config->get('lab_migration_emails', '');
            $cc = $config->get('lab_migration_cc_emails', '');
            $param['standard']['subject'] = $email_subject;
            $param['standard']['body'] = $email_body;
            $param['standard']['headers'] = [
              'From' => $from,
              'MIME-Version' => '1.0',
              'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
              'Content-Transfer-Encoding' => '8Bit',
              'X-Mailer' => 'Drupal',
              'Cc' => $cc,
              'Bcc' => $bcc,
            ];
            if (!drupal_mail('lab_migration', 'standard', $email_to, language_default(), $param, $from, TRUE)) {
              \Drupal::messenger()->addmessage('Error sending email message.', 'error');
            }
          }
        }
        else {
          \Drupal::messenger()->addmessage(t('You do not have permission to bulk manage code.'), 'error');
        }
      }
    }
    return;
  }
  public function _ajax_get_solution_list($lab_experiment_list = '') {
    // return $form['download_solution_wrapper'];
    // $solutions = [
    //   '0' => t('Please select...'),
    // ];
  
    // if (empty($lab_experiment_list)) {
    //   return $solutions;
    // }
  
    // // Query the database to get solutions for the given experiment.
    // $connection = Database::getConnection();
    // $query = $connection->select('lab_migration_solution', 'lms');
    // $query->fields('lms', ['id', 'code_number', 'caption']);
    // $query->condition('experiment_id', $lab_experiment_list);
    // $results = $query->execute();
  
    // // Process the query results and populate the solutions array.
    // foreach ($results as $record) {
    //   $solutions[$record->id] = $record->code_number . ' (' . $record->caption . ')';
    // }
  
    // return $solutions;
  }
  public function _ajax_bulk_get_experiment_list($lab_default_value = '') {
    // return $form['download_lab_wrapper'];
  //   $experiments = [
  //     '0' => 'Please select...',
  //   ];
  
  //   // Get the database connection.
  //   $connection = Database::getConnection();
  
  //   // Prepare the query.
  //   $query = $connection->select('lab_migration_experiment', 'lme')
  //     ->fields('lme', ['id', 'number', 'title'])
  //     ->condition('proposal_id', $lab_default_value)
  //     ->orderBy('number', 'ASC');
  
  //   // Execute the query and fetch results.
  //   $experiments_q = $query->execute();
  // // var_dump($experiment_q);die;
  //   foreach ($experiments_q as $experiments_data) {
  //     $experiments[$experiments_data->id] = $experiments_data->number . '. ' . $experiments_data->title;
  //   }
  
  //   return $experiments;
  }
  public function ajax_solution_list_callback(array &$form, FormStateInterface $form_state) {
    return $form['download_experiment_wrapper'];
  }
  public function _bulk_list_lab_actions(): array {
    return [
      0 => 'Please select...',
      1 => 'Approve Entire Lab',
      2 => 'Pending Review Entire Lab',
      3 => 'Dis-Approve Entire Lab (This will delete all the solutions in the lab)',
      4 => 'Delete Entire Lab Including Proposal',
    ];
  }
  

  public function _bulk_list_of_labs(): array {
    $lab_titles = [
      '0' => 'Please select...',
    ];
  
    // Get the database connection.
    $connection = Database::getConnection();
  
    // Prepare the query.
    $query = $connection->select('lab_migration_proposal', 'lmp')
      ->fields('lmp', ['id', 'lab_title', 'name'])
      ->condition('solution_display', 1)
      ->orderBy('lab_title', 'ASC');
  
    // Execute the query and fetch results.
    $results = $query->execute();
  // var_dump($results);die;
    foreach ($results as $lab_titles_data) {
      $lab_titles[$lab_titles_data->id] = $lab_titles_data->lab_title . ' (Proposed by ' . $lab_titles_data->name . ')';
    }
  // var_dump($lab_titles);die;
    return $lab_titles;
  }
 public function ajax_bulk_experiment_list_callback(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  return $form['update_exp'];
  //   $response = new AjaxResponse();
  
  //   // Get the selected lab value.
  //   $lab_default_value = $form_state->getValue('lab');
  // // var_dump($lab_default_value);die;
  //   if ($lab_default_value != 0) {
  //     // Generate a link for download.
  //     $download_url = Url::fromUserInput('/lab-migration/full-download/lab/' . $lab_default_value);
  //     $download_link = Link::fromTextAndUrl(t('Download'), $download_url)->toString();
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab', $download_link . ' ' . t('(Download all the approved and unapproved solutions of the entire lab)')));
  // // var_dump(hii);die;
  //     // Update lab actions and experiment list.
  //     $form['lab_actions']['#options'] = _bulk_list_lab_actions();
  //     $form['lab_experiment_list']['#options'] = _ajax_bulk_get_experiment_list($lab_default_value);
  
  //     $renderer = \Drupal::service('renderer');
  //     $response->addCommand(new ReplaceCommand('#ajax_selected_experiment', $renderer->render($form['lab_experiment_list'])));
  //     $response->addCommand(new ReplaceCommand('#ajax_selected_lab_action', $renderer->render($form['lab_actions'])));
  
  //     // Clear other sections.
  //     $response->addCommand(new HtmlCommand('#ajax_selected_solution', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_action', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_solution_files', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', ''));
  //   } else {
  //     // Clear all sections if no lab is selected.
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab_pdf', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_selected_experiment', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab_action', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_action', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_selected_lab_experiment_solution_action', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_solution_files', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_download_experiment_solution', ''));
  //     $response->addCommand(new HtmlCommand('#ajax_edit_experiment_solution', ''));
  //   }
  
  //   return $response;
  }

public function _ajax_bulk_get_solution_list($lab_experiment_list = ''): array {
  $solutions = [
    0 => 'Please select...',
  ];

  if (empty($lab_experiment_list)) {
    return $solutions;
  }
// var_dump($lab_experiment_list);die;
  // Get the database connection.
  $connection = Database::getConnection();

  // Prepare the query.
  $query = $connection->select('lab_migration_solution', 'lms')
    ->fields('lms', ['id', 'code_number', 'caption'])
    ->condition('experiment_id', $lab_experiment_list);

  // Add custom ordering logic for `code_number`.
  $query->addExpression("CAST(SUBSTRING_INDEX(code_number, '.', 1) AS BINARY)", 'part1');
  $query->addExpression("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number, '.', 2), '.', -1) AS UNSIGNED)", 'part2');
  $query->addExpression("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code_number, '.', -1), '.', 1) AS UNSIGNED)", 'part3');
  $query->orderBy('part1', 'ASC');
  $query->orderBy('part2', 'ASC');
  $query->orderBy('part3', 'ASC');

  // Execute the query and fetch results.
  $results = $query->execute();
// var_dump($results);die;
  foreach ($results as $solution) {
    $solutions[$solution->id] = $solution->code_number . ' (' . $solution->caption . ')';
  }
var_dump($solutions);die;
  return $solutions;
}

public function _bulk_list_solution_actions(): array {
  return [
    0 => 'Please select...',
    1 => 'Approve Entire Solution',
    2 => 'Pending Review Entire Solution',
    3 => 'Dis-approve Solution (This will delete the solution)',
  ];
}
public function _bulk_list_experiment_actions()
  {
    $lab_experiment_actions = array(
        0 => 'Please select...'
    );
    $lab_experiment_actions[1] = 'Approve Entire Experiment';
    $lab_experiment_actions[2] = 'Pending Review Entire Experiment';
    $lab_experiment_actions[3] = 'Dis-Approve Entire Experiment (This will delete all the solutions in the experiment)';
    return $lab_experiment_actions;
  }
}
?>
