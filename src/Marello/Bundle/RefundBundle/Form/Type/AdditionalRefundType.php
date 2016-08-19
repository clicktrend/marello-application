<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotNull;

class AdditionalRefundType extends AbstractType
{
    const NAME = 'marello_additional_refund';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add('refundAmount', 'oro_money', [
                'empty_data' => 0,
                'constraints' => [
                    new GreaterThan(0),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => RefundItem::class,
            ]
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
