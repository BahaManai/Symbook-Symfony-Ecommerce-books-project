<?php

namespace App\Form;

use App\Entity\Categories;
use Doctrine\DBAL\Types\DateType;
use Faker\Provider\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('libelle', DateType::class)
                // il faut s'assurer from CORE !!!
            ->add('libelle', TextType::class, ['label' => 'Libelle'])
            ->add('slug',TextType::class, ['label' => 'Slug'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
