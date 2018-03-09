<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReCaptchaType extends AbstractType
{
    /**
     * The reCAPTCHA server URL's
     */
    const RECAPTCHA_API_SERVER = 'https://www.google.com/recaptcha/api.js';
    const RECAPTCHA_API_JS_SERVER = '//www.google.com/recaptcha/api/js/recaptcha_ajax.js';

    /**
     * The public key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Enable recaptcha?
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Use AJAX api?
     *
     * @var bool
     */
    protected $ajax;

    /**
     * @param string $publicKey Recaptcha public key
     * @param bool   $enabled   Recaptache status
     * @param bool   $ajax      Ajax status
     */
    public function __construct($publicKey, $enabled, $ajax)
    {
        $this->publicKey = $publicKey;
        $this->enabled = $enabled;
        $this->ajax = $ajax;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            'recaptcha_enabled' => $this->enabled,
            'recaptcha_ajax'    => $this->ajax,
        ));
        if (!$this->enabled) {
            return;
        }

        if (!isset($options['language'])) {
            $options['language'] = $this->resolveLocale();
        }

        if (!$this->ajax) {
            $view->vars = array_replace($view->vars, array(
                'url_challenge' => sprintf('%s?hl=%s', self::RECAPTCHA_API_SERVER, $options['language']),
                'public_key'    => $this->publicKey,
            ));
        } else {
            $view->vars = array_replace($view->vars, array(
                'url_api'    => self::RECAPTCHA_API_JS_SERVER,
                'public_key' => $this->publicKey,
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound'      => false,
            'language'      => $this->resolveLocale(),
            'public_key'    => null,
            'url_challenge' => null,
            'url_noscript'  => null,
            'attr'          => array(
                'options' => array(
                    'theme'           => 'light',
                    'type'            => 'image',
                    'size'            => 'normal',
                    'callback'        => null,
                    'expiredCallback' => null,
                    'bind'            => null,
                    'defer'           => false,
                    'async'           => false,
                    'badge'           => null,
                ),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'recaptcha';
    }

    /**
     * Get the current locale
     *
     * @return string
     */
    public function resolveLocale()
    {
        return 'en';
    }
}
