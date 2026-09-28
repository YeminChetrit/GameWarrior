<?php

namespace App\Form;

use App\Entity\Genre;
use App\Entity\JV;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class JVType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('dateSortie')
            ->add('description')
            ->add('details')
            ->add('image', FileType::class, [
                'label' => 'Image du jeu',
                'mapped' => false,      // le fichier est traité dans le contrôleur
                'required' => false,    // facultatif à la modification (on garde l'ancienne image)
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Merci d\'envoyer une image JPG, PNG ou WebP.',
                    ),
                ],
            ])
            ->add('genre', EntityType::class, [
                'class' => Genre::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JV::class,
        ]);
    }
}