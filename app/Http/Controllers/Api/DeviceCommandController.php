<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FingerprintService;
use Illuminate\Http\Request;

class DeviceCommandController extends Controller
{
    protected $fingerprintService;

    public function __construct(FingerprintService $fingerprintService)
    {
        $this->fingerprintService = $fingerprintService;
    }

    /**
     * Obtener comandos pendientes (Polling del ESP32)
     */
    public function index()
    {
        $commands = $this->fingerprintService->getPendingCommands();
        return response()->json($commands);
    }

    /**
     * Actualizar estado de un comando
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,completed,failed',
            'result' => 'nullable|array'
        ]);

        $command = $this->fingerprintService->updateCommandStatus(
            $id,
            $request->status,
            $request->result
        );

        return response()->json($command);
    }
}
