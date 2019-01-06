<?php

namespace BankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarDumper\VarDumper;

class OperationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('label',null,[
                'attr' => ['placeholder' => "operation.placeholder.label"],
                'label' => 'operation.label.label'
            ])
            ->add('date', DateType::class, [
                'format' => DateType::HTML5_FORMAT,
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['placeholder' => "operation.placeholder.date"],
                'label' => 'operation.label.date'
            ])
            ->add('category', HiddenType::class, [
                'mapped' => false,
                'attr' => ['placeholder' => "operation.placeholder.category"],
                'label' => 'operation.label.category'
            ])
            ->add('category_text', TextType::class, [
                'mapped' => false,
                'attr' => ['placeholder' => "operation.placeholder.category"],
                'label' => 'operation.label.category'
            ])
            ->add('tiers_text', TextType::class, [
                'mapped' => false,
                'attr' => ['placeholder' => "operation.placeholder.tiers"],
                'label' => 'operation.label.tiers'
            ])
            ->add('tiers', HiddenType::class, [
                'mapped' => false,
                'attr' => ['placeholder' => "operation.placeholder.tiers"],
                'label' => 'operation.label.tiers'
            ])
            ->add('referenceCheque', null,[
                'required' => false,
                'attr' => ['placeholder' => "operation.placeholder.referenceCheque"],
                'label' => 'operation.label.referenceCheque'
            ])
            ->add('amount',null,[
                'required' => true,
                'attr' => ['placeholder' => "operation.placeholder.amount"],
                'label' => 'operation.label.amount'
            ])
            ->add('pointed', null, [
                'required' => false,
                'attr' => ['placeholder' => "operation.placeholder.pointed"],
                'label' => 'operation.label.pointed'
            ])
            ->add('budget', null, ['required' => false,
                'attr' => ['placeholder' => "operation.placeholder.budget"],
                'label' => 'operation.label.budget'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BankBundle\Entity\Operation'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bankbundle_operation';
    }


}
