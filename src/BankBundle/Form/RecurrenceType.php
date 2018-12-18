<?php
namespace BankBundle\Form;

use BankBundle\Entity\Recurrence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RecurrenceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('label', null, ['label'=>'recurrence.label.label'])
            ->add('category',HiddenType::class,['mapped'=>false])
            ->add('category_text',TextType::class,['mapped'=>false,'label'=>'recurrence.label.category_text'])
            ->add('tiers_text',TextType::class,['mapped'=>false,'label'=>'recurrence.label.tiers_text'])
            ->add('tiers',HiddenType::class,['mapped'=>false])

            ->add('each',NumberType::class,['label'=>'recurrence.label.each'])
            ->add('type',ChoiceType::class,[
                'choices'=>[
                    "Jour(s)"=>Recurrence::TYPE_DAY,
                    "Mois"=>Recurrence::TYPE_MONTH,
                    "An(s)"=>Recurrence::TYPE_YEAR,
                ],
                'label'=>'recurrence.label.type'
            ])

            ->add('startDate', DateType::class, [
                'format' => DateType::HTML5_FORMAT,
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['placeholder' => "recurrence.placeholder.startDate"],
                'label' => 'recurrence.label.startDate'
            ])
            ->add('endDate', DateType::class, [
                'format' => DateType::HTML5_FORMAT,
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['placeholder' => "recurrence.placeholder.endDate"],
                'label' => 'recurrence.label.endDate'
            ])
            ->add('amount', TextType::class, ['label'=>'recurrence.label.amount'])
            ->add('budget', CheckboxType::class, ['label'=>'recurrence.label.budget'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BankBundle\Entity\Recurrence'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bankbundle_recurrence';
    }


}
