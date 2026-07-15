<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UsuariosRPT;
use App\Services\PayrollFilenameParser;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class NotificacionesController extends Controller
{
    /** @var PayrollFilenameParser */
    private $filenameParser;

    public function __construct(PayrollFilenameParser $filenameParser)
    {
        $this->filenameParser = $filenameParser;
    }

    /**
     * Lista notificaciones del empleado (misma tabla SQL que la campana web).
     */
    public function index(Request $request)
    {
        try {
            $rptUser = $this->getRptUser();
            if (!$rptUser) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Usuario no encontrado.'], 401);
            }

            $soloNoLeidas = filter_var($request->query('unread', false), FILTER_VALIDATE_BOOLEAN);

            $sql = "
                SELECT id, data, read_at, created_at
                FROM notifications
                WHERE notifiable_id = ?
            ";
            $params = [$rptUser->id];

            if ($soloNoLeidas) {
                $sql .= " AND read_at IS NULL";
            }

            $sql .= " ORDER BY created_at DESC";

            $rows = DB::select($sql, $params);

            $data = array_map(function ($row) {
                return $this->mapNotification($row);
            }, $rows);

            return response()->json([
                'Status' => 'Valido',
                'data' => $data,
                'unreadCount' => $this->countUnread($rptUser->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cargar notificaciones. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function unreadCount()
    {
        try {
            $rptUser = $this->getRptUser();
            if (!$rptUser) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Usuario no encontrado.'], 401);
            }

            $count = $this->countUnread($rptUser->id);

            return response()->json([
                'Status' => 'Valido',
                'unreadCount' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible obtener el conteo. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function markAsRead(Request $request, $id = null)
    {
        try {
            $rptUser = $this->getRptUser();
            if (!$rptUser) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Usuario no encontrado.'], 401);
            }

            $notificationId = $id ?: $request->input('id');
            if (!$notificationId) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Id de notificación requerido.'], 422);
            }

            $now = Carbon::now()->format('Ymd H:i:s');

            DB::update("
                UPDATE notifications
                SET read_at = ?, updated_at = ?
                WHERE id = ?
                    AND notifiable_id = ?
                    AND read_at IS NULL
            ", [$now, $now, $notificationId, $rptUser->id]);

            return response()->json([
                'Status' => 'Valido',
                'unreadCount' => $this->countUnread($rptUser->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible marcar la notificación. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $rptUser = $this->getRptUser();
            if (!$rptUser) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Usuario no encontrado.'], 401);
            }

            $now = Carbon::now()->format('Ymd H:i:s');

            DB::update("
                UPDATE notifications
                SET read_at = ?, updated_at = ?
                WHERE notifiable_id = ?
                    AND read_at IS NULL
            ", [$now, $now, $rptUser->id]);

            return response()->json([
                'Status' => 'Valido',
                'unreadCount' => 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible marcar las notificaciones. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rptUser = $this->getRptUser();
            if (!$rptUser) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Usuario no encontrado.'], 401);
            }

            if (!$id) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Id de notificación requerido.'], 422);
            }

            $deleted = DB::delete("
                DELETE FROM notifications
                WHERE id = ?
                    AND notifiable_id = ?
            ", [$id, $rptUser->id]);

            if (!$deleted) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'Notificación no encontrada.',
                ], 404);
            }

            return response()->json([
                'Status' => 'Valido',
                'Mensaje' => 'Notificación eliminada.',
                'unreadCount' => $this->countUnread($rptUser->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible eliminar la notificación. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAll()
    {
        try {
            $rptUser = $this->getRptUser();
            if (!$rptUser) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Usuario no encontrado.'], 401);
            }

            DB::delete("
                DELETE FROM notifications
                WHERE notifiable_id = ?
            ", [$rptUser->id]);

            return response()->json([
                'Status' => 'Valido',
                'Mensaje' => 'Notificaciones eliminadas.',
                'unreadCount' => 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible eliminar las notificaciones. ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getRptUser()
    {
        $userdata = auth()->user();
        if (!$userdata) {
            return null;
        }

        $nomina = isset($userdata['USU_Nombre']) ? $userdata['USU_Nombre'] : null;
        if ($nomina === null || $nomina === '') {
            return null;
        }

        $variants = $this->filenameParser->employeeCodeVariants($nomina);
        if (empty($variants)) {
            return UsuariosRPT::where('nomina', $nomina)->first();
        }

        return UsuariosRPT::whereIn('nomina', $variants)->first();
    }

    private function countUnread($userId)
    {
        $row = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM notifications
            WHERE notifiable_id = ?
                AND read_at IS NULL
        ", [$userId]);

        return $row ? (int) $row->total : 0;
    }

    private function mapNotification($row)
    {
        $data = $row->data;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        } elseif (is_object($data)) {
            $data = (array) $data;
        } elseif (!is_array($data)) {
            $data = [];
        }

        $title = isset($data['title']) ? $data['title'] : '';
        $body = isset($data['body']) ? $data['body'] : (isset($data['msg']) ? $data['msg'] : '');

        return [
            '_id' => (string) $row->id,
            'title' => $title,
            'msg' => $body,
            'createdDate' => $this->formatDateIso($row->created_at),
            'isRead' => !empty($row->read_at),
            'action' => isset($data['action']) ? $data['action'] : null,
        ];
    }

    private function formatDateIso($value)
    {
        if (empty($value)) {
            return Carbon::now()->utc()->format('Y-m-d\TH:i:s.000\Z');
        }

        return Carbon::parse($value)->utc()->format('Y-m-d\TH:i:s.000\Z');
    }
}
