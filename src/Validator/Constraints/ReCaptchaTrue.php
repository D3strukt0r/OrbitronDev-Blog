<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class ReCaptchaTrue extends Constraint
{
    public $message = 'The captcha was not correct.';
    public $invalidHostMessage = 'The captcha was not resolved on the right domain.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'recaptcha.true';
    }
}
