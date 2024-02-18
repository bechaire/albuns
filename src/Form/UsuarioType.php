<?php

namespace App\Form;

use App\DTO\UsuarioInputDTO;
use App\Enum\RolesEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('usuario', options: ['label'=>'Nome do usuário (como no AD/LDAP)'])
            ->add('nome', options: ['label'=>'Nome completo'])
            ->add('email', options: ['label'=>'E-mail para alertas'])
            ->add('papel', ChoiceType::class, [
                'label' => 'Papel (nível de acesso)',
                'help' => 'Administrador pode criar usuários',
                'choices' => array_flip(RolesEnum::toAssociativeArray())
            ])
            ->add('ativo', ChoiceType::class, [
                'choices' => [
                    'Sim' => 'S',
                    'Não' => 'N'
                ],
                'expanded' => true,
                'attr' => [
                    'class' => 'd-flex app-form-check-inline'
                ]
            ])
            ->add('save', SubmitType::class, ['label' => $options['is_edit'] ? 'Salvar Alterações' : 'Adicionar Usuário'])
            ->setMethod($options['is_edit'] ? 'PATCH' : 'POST')
        ;
        $builder->get('email')->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $email = $event->getData();
            $event->setData(strtolower($email));
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsuarioInputDTO::class,
            'is_edit' => false
        ]);
    }
}
