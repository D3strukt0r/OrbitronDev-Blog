<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content',
                TextareaType::class,
                [
                    'label' => 'Comment',
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter a message']),
                    ],
                ]
            )
            ->add(
                'send',
                SubmitType::class,
                [
                    'label' => 'service_post.comment.add',
                ]
            )
        ;
    }
}
