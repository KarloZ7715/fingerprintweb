<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validación para almacenar huella desde ESP32
 */
class StoreFingerprintRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para esta request
     * 
     * API pública (ESP32), sin autenticación por ahora
     * TODO: Implementar autenticación con token cuando se configure ESP32
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'empleado_id' => [
                'required',
                'integer',
                'exists:empleado,id',
            ],
            'slot_id' => [
                'required',
                'integer',
                'min:0',
                'max:299',
                'unique:huella,numero_slot',
            ],
            'quality_score' => [
                'required',
                'integer',
                'min:0',
                'max:255',
            ],
            'admin_id' => [
                'nullable',
                'integer',
                'exists:administrador,id',
            ],
            'tipo_dedo' => [
                'nullable',
                'string',
                'in:Pulgar,Indice,Medio,Anular,Meñique',
            ],
            'mano' => [
                'nullable',
                'string',
                'in:Izquierda,Derecha',
            ],
        ];
    }

    /**
     * Mensajes de error personalizados en español
     */
    public function messages(): array
    {
        return [
            'empleado_id.required' => 'El ID del empleado es obligatorio',
            'empleado_id.exists' => 'El empleado no existe en la base de datos',
            'slot_id.required' => 'El slot ID es obligatorio',
            'slot_id.min' => 'El slot ID debe ser mayor o igual a 0',
            'slot_id.max' => 'El slot ID debe ser menor o igual a 299',
            'slot_id.unique' => 'El slot :input ya está ocupado',
            'quality_score.required' => 'El puntaje de calidad es obligatorio',
            'quality_score.min' => 'El puntaje de calidad debe ser mayor o igual a 0',
            'quality_score.max' => 'El puntaje de calidad debe ser menor o igual a 255',
            'admin_id.exists' => 'El administrador no existe en la base de datos',
            'tipo_dedo.in' => 'El tipo de dedo debe ser: Pulgar, Indice, Medio, Anular o Meñique',
            'mano.in' => 'La mano debe ser: Izquierda o Derecha',
        ];
    }

    /**
     * Nombres de atributos personalizados
     */
    public function attributes(): array
    {
        return [
            'empleado_id' => 'ID de empleado',
            'slot_id' => 'slot',
            'quality_score' => 'calidad',
            'admin_id' => 'ID de administrador',
            'tipo_dedo' => 'tipo de dedo',
            'mano' => 'mano',
        ];
    }
}
