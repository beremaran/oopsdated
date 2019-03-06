<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 14:39
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repoName', TextType::class, [
                'required' => true,
                'label' => 'A Github Repository please:',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('emailAddress', EmailType::class, [
                'required' => true,
                'label' => 'What\'s your e-mail address?',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('Subscribe', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

}