<?php

namespace App\Form;

use App\Form\Type\ReCaptchaType;
use App\Validator\Constraints\ReCaptchaTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewBlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'       => 'new_blog.form.name.label',
                'constraints' => [
                    new NotBlank(['message' => 'new_blog.form.name.constraints.not_blank']),
                ],
            ])
            ->add('url', TextType::class, [
                'label'       => 'new_blog.form.url.label',
                'constraints' => [
                    new NotBlank(['message' => 'new_blog.form.url.constraints.not_blank']),
                ],
            ])
            ->add('recaptcha', ReCaptchaType::class, [
                'attr'        => [
                    'options' => [
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal',
                        'defer' => true,
                        'async' => true,
                    ],
                ],
                'mapped'      => false,
                'constraints' => [
                    new ReCaptchaTrue(),
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'new_blog.form.send.label',
            ]);
    }
}
