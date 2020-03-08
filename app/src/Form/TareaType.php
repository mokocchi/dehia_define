<?php

namespace App\Form;

use App\Entity\Tarea;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TareaType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('nombre')
      ->add('consigna')
      ->add('codigo')
      ->add('tipo')
      ->add('dominio')
      ->add('estado')
      ->add('extraData', ExtraType::class, ['mapped' => false]);
  }
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => Tarea::class,
      'csrf_protection' => false
    ));
  }
}
