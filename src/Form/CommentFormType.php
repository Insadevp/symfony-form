<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;



class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('author', null, [
                'label' => 'Votre Nom',

            ])
            ->add('content', TextType::class)

            ->add('email', EmailType::class)

            /* ->add('rgpd', CheckboxType::class, [
                'label' => 'J'accepte que mes informations soient stockées dans la base de données de Mon Blog pour la gestion des commentaires. J'ai bien noté qu'en aucun cas ces données ne seront cédées à des tiers.'
            ]) */
            ->add('envoyer', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,

            'submitBtn' => 'Validate',
            // enable/disable CSRF protection for this form
            'csrf_protection' => true,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_token',
            // an arbitrary string used to generate the value of the token
            // using a different string for each form improves its security
            'csrf_token_id'   => 'comment_item',


        ]);
    }
}