<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\InstituicaoRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InstituicaoChoiceValidator extends ConstraintValidator
{
    public function __construct(
        private InstituicaoRepository $instituicaoRepository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        $choices = $this->instituicaoRepository->getSiglas();

        if (!\in_array($value, $choices)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
