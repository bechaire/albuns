<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\AlbumInputDTO;
use App\Enum\AlbumStatusEnum;
use App\Repository\InstituicaoRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlbumType extends AbstractType
{
    public function __construct(
        private InstituicaoRepository $instituicaoRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $siglasInstituicoes = $this->instituicaoRepository->getSiglas();

        $builder
            ->add('instituicao', ChoiceType::class, [
                'label' => 'Instituição',
                'choices' => array_combine($siglasInstituicoes, $siglasInstituicoes),
                //'error_bubbling' => true,
                'invalid_message' => 'Valor inválido para o campo INSTITUIÇÃO',
            ])
            ->add('titulo', options: [
                'label' => 'Título do Álbum'
            ])
            ->add('data', DateType::class, options: [
                'label' => 'Data da Atividade',
                // 'input'  => 'datetime',
            ])
            ->add('local', options: [
                'label' => 'Local das Fotos'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status do álbum',
                'choices' => array_flip(AlbumStatusEnum::toAssociativeArray())
            ])
            ->add('addtag', ChoiceType::class, [
                'label' => 'Adiciona TAG',
                'choices' => [
                    'Sim' => 'S',
                    'Não' => 'N'
                ],
                'expanded' => true,
                'attr' => [
                    'class' => 'd-flex app-form-check-inline'
                ]
            ])
            ->add('criador', TextType::class, options: [
                'label' => 'Criado por',
                'required' => false,
                'disabled' => true,
            ])
            ->add('created', DateTimeType::class,[
                'label' => 'Criado em',
                'required' => false,
                'disabled' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['is_edit'] ? 'Salvar Alterações' : 'Adicionar Álbum'
            ])
            ->setMethod($options['is_edit'] ? 'PATCH' : 'POST');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AlbumInputDTO::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
