<?php

namespace App\Http\Controllers\CapitalHumano;

use App\Http\Controllers\Controller;
use App\Models\RPT\RPT_PAYROLL;
use App\Services\PayrollFilenameParser;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class NominaController extends Controller
{
    const STATUS_NEW = 'new';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /** @var PayrollFilenameParser */
    private $filenameParser;

    public function __construct(PayrollFilenameParser $filenameParser)
    {
        $this->filenameParser = $filenameParser;
    }

    public function misRecibos()
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $variants = $this->filenameParser->employeeCodeVariants($empleado);
            if (empty($variants)) {
                return response()->json(['Status' => 'Valido', 'data' => []]);
            }

            $placeholders = implode(',', array_fill(0, count($variants), '?'));
            $rows = DB::select("
                SELECT
                    PAY_PayrollId,
                    PAY_NombreArchivo,
                    PAY_Estatus,
                    PAY_Observacion,
                    PAY_Visto,
                    PAY_FechaVisto,
                    PAY_Descargado,
                    PAY_FechaDescarga,
                    PAY_Folio,
                    PAY_FechaNomina,
                    PAY_FechaAceptRech,
                    PAY_RutaArchivo
                FROM RPT_PAYROLLS
                WHERE PAY_EmpleadoCodigo IN ({$placeholders})
                ORDER BY PAY_FechaNomina DESC, PAY_FechaCreacion DESC
            ", $variants);

            $data = array_map(function ($row) {
                return $this->mapPayrollToApp($row);
            }, $rows);

            return response()->json([
                'Status' => 'Valido',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cargar los recibos de nómina. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function actualizarEstatus(Request $request, $id)
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $status = $request->input('status');
            $observation = $request->input('observation');

            if (!in_array($status, [self::STATUS_ACCEPTED, self::STATUS_REJECTED], true)) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Estatus no válido.'], 422);
            }

            if ($status === self::STATUS_REJECTED) {
                $obsLength = mb_strlen(trim((string) $observation));
                if ($obsLength < 20 || $obsLength > 100) {
                    return response()->json([
                        'Status' => 'Error',
                        'Mensaje' => 'La observación debe tener entre 20 y 100 caracteres.',
                    ], 422);
                }
            }

            $payroll = $this->findPayrollForEmployee($id, $empleado);
            if (!$payroll) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Recibo de nómina no encontrado.'], 404);
            }

            if (in_array($payroll->PAY_Estatus, [self::STATUS_ACCEPTED, self::STATUS_REJECTED], true)) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'Este recibo ya fue aceptado o rechazado.',
                ], 409);
            }

            $now = Carbon::now();
            $update = [
                'PAY_Estatus' => $status,
                'PAY_FechaAceptRech' => $now,
            ];

            if ($status === self::STATUS_REJECTED) {
                $update['PAY_Observacion'] = trim((string) $observation);
                $update['PAY_Folio'] = $this->generateFolio();
            } else {
                $update['PAY_Observacion'] = null;
                $update['PAY_Folio'] = null;
            }

            RPT_PAYROLL::where('PAY_PayrollId', $id)->update($update);

            return response()->json([
                'Status' => 'Valido',
                'Mensaje' => 'Recibo actualizado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible actualizar el recibo. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descarga/visualización del PDF con JWT.
     * Solo el empleado dueño del recibo puede acceder.
     */
    public function descargarArchivo($id)
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $payroll = $this->findPayrollForEmployee($id, $empleado);
            if (!$payroll) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Recibo de nómina no encontrado.'], 404);
            }

            $path = $this->resolvePayrollFilePath($payroll);
            if (!$path || !is_readable($path)) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'El archivo del recibo no está disponible en el servidor.',
                ], 404);
            }

            $fileName = basename($payroll->PAY_NombreArchivo);

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible obtener el recibo. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function marcarVistoDescargado(Request $request, $id)
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            if (!$request->has('isViewed') || !$request->has('isDownloaded')) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Parámetros incompletos.'], 422);
            }

            $isViewed = filter_var($request->input('isViewed'), FILTER_VALIDATE_BOOLEAN);
            $isDownloaded = filter_var($request->input('isDownloaded'), FILTER_VALIDATE_BOOLEAN);

            $payroll = $this->findPayrollForEmployee($id, $empleado);
            if (!$payroll) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Recibo de nómina no encontrado.'], 404);
            }

            $update = [
                'PAY_Visto' => $isViewed ? 1 : 0,
                'PAY_Descargado' => $isDownloaded ? 1 : 0,
            ];

            $now = Carbon::now();
            if ($isViewed && empty($payroll->PAY_FechaVisto)) {
                $update['PAY_FechaVisto'] = $now;
            }
            if ($isDownloaded && empty($payroll->PAY_FechaDescarga)) {
                $update['PAY_FechaDescarga'] = $now;
            }

            RPT_PAYROLL::where('PAY_PayrollId', $id)->update($update);

            return response()->json([
                'Status' => 'Valido',
                'Mensaje' => 'Recibo actualizado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible actualizar el recibo. ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getCodigoEmpleado()
    {
        $userdata = auth()->user();

        return $userdata ? $userdata['USU_Nombre'] : null;
    }

    private function findPayrollForEmployee($id, $empleado)
    {
        $variants = $this->filenameParser->employeeCodeVariants($empleado);
        if (empty($variants)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($variants), '?'));
        $params = array_merge([$id], $variants);

        return DB::selectOne("
            SELECT TOP 1
                PAY_PayrollId,
                PAY_NombreArchivo,
                PAY_RutaArchivo,
                PAY_Estatus,
                PAY_FechaVisto,
                PAY_FechaDescarga
            FROM RPT_PAYROLLS
            WHERE PAY_PayrollId = ?
                AND PAY_EmpleadoCodigo IN ({$placeholders})
        ", $params);
    }

    private function mapPayrollToApp($row)
    {
        $payrollId = (string) $row->PAY_PayrollId;
        $apiUrl = $this->getPublicApiBaseUrl()
            . '/api/capital-humano/nomina/recibos/'
            . $payrollId
            . '/archivo';

        return [
            '_id' => $payrollId,
            'name' => $row->PAY_NombreArchivo,
            'status' => $row->PAY_Estatus,
            'observation' => $row->PAY_Observacion,
            'isViewed' => (bool) $row->PAY_Visto,
            'viewedDate' => $this->formatDateIso($row->PAY_FechaVisto),
            'isDownloaded' => (bool) $row->PAY_Descargado,
            'downloadedDate' => $this->formatDateIso($row->PAY_FechaDescarga),
            'folio' => $row->PAY_Folio,
            'createdDate' => $this->formatDateIso($row->PAY_FechaNomina),
            'url' => $apiUrl,
            'acceptedOrRejectedDate' => $this->formatDateIso($row->PAY_FechaAceptRech),
        ];
    }

    /**
     * Base URL que la app puede alcanzar (WAN), no la IP LAN interna.
     * Prioridad: NOMINA_PUBLIC_API_URL → host de la petición → APP_URL.
     */
    private function getPublicApiBaseUrl()
    {
        $configured = config('nomina.public_api_url');
        if (!empty($configured)) {
            return rtrim($configured, '/');
        }

        try {
            if (request()) {
                return rtrim(request()->root(), '/');
            }
        } catch (\Exception $e) {
            // fallback abajo
        }

        return rtrim(config('app.url'), '/');
    }

    private function resolvePayrollFilePath($payroll)
    {
        $basePath = rtrim(config('nomina.storage_absolute_path'), DIRECTORY_SEPARATOR);
        $fileName = basename($payroll->PAY_NombreArchivo);

        $candidates = [
            $basePath . DIRECTORY_SEPARATOR . $fileName,
        ];

        if (!empty($payroll->PAY_RutaArchivo)) {
            $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $payroll->PAY_RutaArchivo);
            $candidates[] = $basePath . DIRECTORY_SEPARATOR . basename($relative);
        }

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function formatDateIso($value)
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->utc()->format('Y-m-d\TH:i:s.000\Z');
    }

    private function generateFolio()
    {
        $count = DB::table('RPT_PAYROLLS')
            ->where('PAY_Estatus', self::STATUS_REJECTED)
            ->count() + 1;

        $format = config('nomina.folio_format', '00000');
        $countStr = (string) $count;

        return substr($format, 0, -strlen($countStr)) . $countStr;
    }
}
