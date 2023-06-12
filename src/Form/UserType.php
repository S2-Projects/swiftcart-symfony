<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('name')
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters.',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please confirm your password.',
                    ]),
                    new Callback([$this, 'validatePasswordConfirmation']),
                ],
            ]);
    }

    public function validatePasswordConfirmation($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();

        if ($value !== $form->get('password')->getData()) {
            $context->buildViolation('The password fields must match.')
                ->atPath('confirm_password')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}