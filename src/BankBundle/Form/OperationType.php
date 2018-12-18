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
            ->add('label')
            ->add('date', DateType::class, [
                'format' => DateType::HTML5_FORMAT,
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['placeholder' => "operation.placeholder.date"],
                'label' => 'operation.placeholder.date'
            ])
            ->add('category',HiddenType::class,['mapped'=>false])
            ->add('category_text',TextType::class,['mapped'=>false])
            ->add('tiers_text',TextType::class,['mapped'=>false])
            ->add('tiers',HiddenType::class,['mapped'=>false])
            ->add('referenceCheque')
            ->add('amount')
            ->add('pointed')
            ->add('budget')
        ;
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
