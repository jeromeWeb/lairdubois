<?php

namespace Ladb\CoreBundle\Form\Type\Message;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Ladb\CoreBundle\Form\DataTransformer\UsersToUsernamesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Form\Type\UsernameFormType;
use Ladb\CoreBundle\Form\DataTransformer\PicturesToIdsTransformer;

class NewThreadMessageType extends AbstractType
{

    private $om;
    private $userManager;

    public function __construct(ObjectManager $om, UserManagerInterface $userManager)
    {
        $this->om = $om;
        $this->userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder
                ->create('recipients', TextType::class, array( 'attr' => array( 'class' => 'ladb-pseudo-hidden2' ) ))
                ->addModelTransformer(new UsersToUsernamesTransformer($this->userManager)))
            ->add('subject')
            ->add('body', TextareaType::class)
            ->add($builder
                ->create('pictures', TextType::class, array( 'attr' => array( 'class' => 'ladb-pseudo-hidden' ) ))
                ->addModelTransformer(new PicturesToIdsTransformer($this->om)))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ladb\CoreBundle\Form\Model\NewThreadMessage'
        ));
    }

    public function getBlockPrefix()
    {
        return 'ladb_message_newthread';
    }
}
