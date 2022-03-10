<?php

namespace Drupal\gm_mailing_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\Entity\Media;
use Drupal\image\Entity\ImageStyle;
use Drupal\media_entity\MediaInterface;

/**
 * Provides a 'MailingListlBlock' block.
 *
 * @Block(
 *  id = "mailig_list_block",
 *  admin_label = @Translation("Mailing list block"),
 * )
 */
class MailingListlBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $bg_newlleter_default = NULL;
    $bg_newlleter = !empty($config['bk_bg']) ? $config['bk_bg'] : NULL;
    if ($bg_newlleter) {
      $bg_newlleter_default = Media::load($bg_newlleter);
    }

    $form['bk_bg'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Background block'),
      '#target_type'   => 'media',
      '#default_value' => $bg_newlleter_default,
      '#allowed_bundles' => ['image'],
    ];

    $form['bk_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Background color'),
      '#default_value' => !empty($config['bk_bg_color']) ? $config['bk_bg_color'] : '#FFFFFF',
    ];

    $form['bk_text_color'] = [
      '#type' => 'select',
      '#title' => 'Text color',
      '#options' => [
        'gm-light' => 'light',
        'gm-dark' => 'dark',
      ],
      '#default_value' => !empty($config['bk_text_color']) ? $config['bk_text_color'] : 0,
    ];

    $form['bk_baseline'] = [
      '#type' => 'textfield',
      '#title' => 'Baseline',
      '#default_value' => !empty($config['bk_baseline']) ? $config['bk_baseline'] : '',
    ];

    $form['bk_accept_text'] = [
      '#type' => 'text_format',
      '#title' => 'consentement',
      '#format' => 'mailer',
      '#default_value' => !empty($config['bk_accept_text']) ? $config['bk_accept_text'] : '',
    ];

    $form['bk_padding'] = [
      '#type' => 'select',
      '#title' => 'Padding',
      '#options' => [
        'padding-remove' => 'Remove',
        'section-xsmall' => 'xsmall',
        'section-small' => 'small',
        'section-large' => 'large',
        'section-xlarge' => 'xlarge',
      ],
      '#default_value' => !empty($config['bk_padding']) ? $config['bk_padding'] : 'padding-remove',
    ];

    $form['bk_bg_overlay'] = [
      '#title' => t('Overlay'),
      '#type' => 'select',
      '#options' => [
        '0' => '0',
        '1' => '.1',
        '2' => '.2',
        '3' => '.3',
        '4' => '.4',
        '4' => '.4',
        '5' => '.5',
        '6' => '.6',
        '7' => '.7',
        '8' => '.8',
        '9' => '.9',
        '10' => '1',
      ],
      '#default_value' => !empty($config['bk_bg_overlay']) ? $config['bk_bg_overlay'] : '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    // if($form_state->getValue('hello_block_name') === 'John'){
    //   $form_state->setErrorByName('hello_block_name', $this->t('You can not say hello to John.'));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['bk_bg'] = $values['bk_bg'];
    $this->configuration['bk_bg_color'] = $values['bk_bg_color'];
    $this->configuration['bk_text_color'] = $values['bk_text_color'];
    $this->configuration['bk_bg_overlay'] = $values['bk_bg_overlay'];
    $this->configuration['bk_padding'] = $values['bk_padding'];
    $this->configuration['bk_baseline'] = $values['bk_baseline'];
    $this->configuration['bk_accept_text'] = $values['bk_accept_text']['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $form_to_load = '\Drupal\gm_mailing_list\Form\MailingListForm';
    $config = $this->getConfiguration();

    $bg_page = NULL;
    $bk_bg_newlleter = !empty($config['bk_bg']) ? $config['bk_bg'] : NULL;
    
    if ($bk_bg_newlleter) {
      $bg = Media::load($bk_bg_newlleter);
      if ($bg) {
        /** @var \Drupal\media\MediaInterface $bg */
        $media_uri = $bg->field_media_image->entity->getFileUri();
        $bg_page = ImageStyle::load('full')->buildUrl($media_uri);
      }
    }

    $bg_color = isset($config['bk_bg_color'] ) ? $config['bk_bg_color'] : '#FFFFFF';
    $text_color = !empty($config['bk_text_color']) ? $config['bk_text_color'] : 'gm-dark';
    $bg_overlay = !empty($config['bk_bg_overlay']) ? 'gm-ovelay-' . $config['bk_bg_overlay'] : 'gm-ovelay-0';
    $bk_padding = !empty($config['bk_padding']) ? 'gm-bk-' . $config['bk_padding'] : 'gm-bk-padding-remove';
    $bk_baseline = !empty($config['bk_baseline']) ? $config['bk_baseline'] : NULL;
    $bk_accept_text = !empty($config['bk_accept_text']) ? $config['bk_accept_text'] : NULL;

    return array(
      '#theme' => 'newsletter_form',
      '#bk_bg' => $bg_page,
      '#bk_bg_color' => $bg_color,
      '#bk_text_color' => $text_color,
      '#bk_bg_overlay' => $bg_overlay,
      '#bk_padding' => $bk_padding,
      '#bk_baseline' => $bk_baseline,
      '#bk_accept_text' => $bk_accept_text
    );
  }
}
