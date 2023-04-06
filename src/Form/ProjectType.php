<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Merci de décrire votre projet...',
                ],
            ])
            ->add('image', ImageType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add(
                'categories',
                EntityType::class,
                [
                    'class'         => Category::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ca')
                            ->orderBy('ca.name', 'DESC');
                    },
                    /*'expanded' => true,*/
                    'multiple'      => true,
                    'choice_label'   => 'name',
                    'expanded' => false,
                    'required'    => true,
                    'attr' => [
                        'class' => 'select2',
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
