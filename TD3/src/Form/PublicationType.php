<?php

namespace App\Form;

use App\Entity\Publication;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationType extends AbstractType
{
    public function __construct(
        private Security $security   // Service fictif
    ) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('publier', SubmitType::class)
            ->add('message', TextareaType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 200
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        if(!is_null($this->security->getUser())){
            $group = $this->security->getUser()->isPremium() ? 'publication:write:premium' : 'publication:write:normal';
        }else{
            $group = "";
        }
        $resolver->setDefaults([
            'data_class' => Publication::class,
            'validation_groups' => ['Default', $group]
        ]);
    }
}
