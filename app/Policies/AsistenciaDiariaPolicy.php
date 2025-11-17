<?php

namespace App\Policies;

use App\Models\Administrador;
use App\Models\AsistenciaDiaria;
use Illuminate\Auth\Access\Response;

class AsistenciaDiariaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Administrador $administrador): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Administrador $administrador, AsistenciaDiaria $asistenciaDiaria): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Administrador $administrador): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Administrador $administrador, AsistenciaDiaria $asistenciaDiaria): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Administrador $administrador, AsistenciaDiaria $asistenciaDiaria): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Administrador $administrador, AsistenciaDiaria $asistenciaDiaria): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Administrador $administrador, AsistenciaDiaria $asistenciaDiaria): bool
    {
        return false;
    }
}
