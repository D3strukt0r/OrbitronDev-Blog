<?php

namespace App\Validator\Constraints;

use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReCaptchaTrueValidator extends ConstraintValidator
{
    /**
     * Enable recaptcha?
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Recaptcha Private Key.
     *
     * @var string
     */
    protected $privateKey;

    /**
     * Request Stack.
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * HTTP Proxy informations.
     *
     * @var array
     */
    protected $httpProxy;

    /**
     * Enable serverside host check.
     *
     * @var bool
     */
    protected $verifyHost;

    /**
     * @param bool         $enabled
     * @param string       $privateKey
     * @param RequestStack $requestStack
     * @param bool         $verifyHost
     */
    public function __construct($enabled, $privateKey, RequestStack $requestStack, $verifyHost)
    {
        $this->enabled = $enabled;
        $this->privateKey = $privateKey;
        $this->requestStack = $requestStack;
        $this->verifyHost = $verifyHost;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        // if recaptcha is disabled, always valid
        if (!$this->enabled) {
            return;
        }

        // define variable for recaptcha check answer
        $masterRequest = $this->requestStack->getMasterRequest();
        $remoteip = $masterRequest->getClientIp();
        $answer = $masterRequest->get('g-recaptcha-response');

        // Verify user response with Google
        $recaptcha = new ReCaptcha($this->privateKey);
        $response = $recaptcha->verify($answer, $remoteip);

        if (!$response->isSuccess()) {
            $this->context->addViolation($constraint->message);
        }

        // Perform server side hostname check
        elseif ($this->verifyHost && $response['hostname'] !== $masterRequest->getHost()) {
            $this->context->addViolation($constraint->invalidHostMessage);
        }
    }
}
