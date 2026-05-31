<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Petite classe de validation des données de formulaire.
 * Accumule les erreurs sous forme de messages lisibles.
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        if (trim((string) ($this->data[$field] ?? '')) === '') {
            $this->errors[$field] = "Le champ « {$label} » est obligatoire.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "L'adresse e-mail n'est pas valide.";
        }
        return $this;
    }

    /**
     * Vérifie qu'un mot de passe est « fort » :
     * 10 caractères minimum, au moins une majuscule, une minuscule,
     * un chiffre et un caractère spécial.
     */
    public function strongPassword(string $field): self
    {
        $value = (string) ($this->data[$field] ?? '');
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/';
        if (!preg_match($regex, $value)) {
            $this->errors[$field] =
                'Le mot de passe doit contenir au moins 10 caractères, '
                . 'une majuscule, une minuscule, un chiffre et un caractère spécial.';
        }
        return $this;
    }

    public function matches(string $field, string $otherField, string $label): self
    {
        if (($this->data[$field] ?? null) !== ($this->data[$otherField] ?? null)) {
            $this->errors[$field] = "Les champs « {$label} » ne correspondent pas.";
        }
        return $this;
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
