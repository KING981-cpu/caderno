<?php

namespace App\Services;

use App\Models\Localidade;
use App\Models\Usuario;

class ReferenceService
{
    private Localidade $localidadeModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->localidadeModel = new Localidade();
        $this->usuarioModel = new Usuario();
    }

    public function getLocalidades(): array
    {
        try {
            return $this->localidadeModel->getAllOrderedByName();
        } catch (\Exception $e) {
            \App\SRE\Logger::error('Failed to get localidades: ' . $e->getMessage());
            return [];
        }
    }

    public function getUsuarios(): array
    {
        try {
            return $this->usuarioModel->all();
        } catch (\Exception $e) {
            \App\SRE\Logger::error('Failed to get usuarios: ' . $e->getMessage());
            return [];
        }
    }

    public function createLocalidade(string $nome): ?int
    {
        try {
            // Check for duplicates
            if ($this->localidadeModel->findByName($nome)) {
                throw new \Exception('Localidade already exists');
            }

            $id = $this->localidadeModel->create(['nome' => $nome]);
            \App\SRE\Logger::info('Localidade created', ['id' => $id]);
            return (int)$id;
        } catch (\Exception $e) {
            \App\SRE\Logger::error('Localidade creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createUsuario(string $nome): ?int
    {
        try {
            $id = $this->usuarioModel->create(['nome' => $nome]);
            \App\SRE\Logger::info('Usuario created', ['id' => $id]);
            return (int)$id;
        } catch (\Exception $e) {
            \App\SRE\Logger::error('Usuario creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
