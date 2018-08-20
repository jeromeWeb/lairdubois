<?php

namespace Ladb\CoreBundle\Form\Type\Knowledge\Value;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignValueType extends AbstractValueType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('brand', TextType::class)
            ->add('isAffiliate', CheckboxType::class)
            ->add('store', TextType::class)
        ;

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $value = $event->getData();
                $value->setBrand(ucfirst($value->getBrand()));
                $value->setStore(ucfirst($value->getStore()));
                if (!$value->getIsAffiliate()) {
                    $value->setStore(null);
                    $value->setData($value->getBrand());
                } else {
                    $value->setData($value->getBrand() . ',' . $value->getStore());
                }
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'Ladb\CoreBundle\Entity\Knowledge\Value\Sign',
        ));
    }

    public function getBlockPrefix()
    {
        return 'ladb_knowledge_value_sign';
    }
}
