<?php

namespace App\Exports;

use App\Congelamiento;
use App\Planpago;
use App\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reporte de clientes: columnas del listado de Clientes más registro, último pago validado
 * y clases reservadas en el periodo del plan actual. Una fila por cliente.
 */
// WithStrictNullComparison: sin esto un 0 (clases, reservas) se escribe como celda vacía
class ClienteExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithStrictNullComparison
{
    protected $clientes;
    protected $pendientes;
    protected $validados;
    protected $congelamientos;

    public function __construct($clientes)
    {
        $this->clientes = $clientes;
        $ids = $clientes->pluck('id')->all();
        // Pago en validación y último pago validado de cada cliente (misma regla que la columna "Pago plan")
        $this->pendientes = Planpago::whereIn('cliente_id', $ids)->where('situacion', '=', 'PENDIENTE')
            ->orderBy('id', 'desc')->get()->unique('cliente_id')->keyBy('cliente_id');
        $this->validados = Planpago::whereIn('cliente_id', $ids)->where('situacion', '=', 'VALIDADO')
            ->orderBy('id', 'desc')->get()->unique('cliente_id')->keyBy('cliente_id');
        $this->congelamientos = Congelamiento::whereIn('cliente_id', $ids)->where('situacion', '=', 'ACTUAL')
            ->orderBy('id', 'desc')->get()->unique('cliente_id')->keyBy('cliente_id');
    }

    public function collection(): Collection
    {
        return $this->clientes;
    }

    public function headings(): array
    {
        return array('DNI', 'Apellidos y Nombres', 'Direccion', 'Fecha Nac.', 'DNI Apoderado', 'Telefono', 'Correo',
            'Plan', 'Clases', 'Situacion', 'Pago plan', 'Fecha de registro', 'Fecha de pago o renovación',
            'Monto Pagado', 'Clases reservadas');
    }

    public function map(mixed $cliente): array
    {
        $pendiente = $this->pendientes->get($cliente->id);
        $validado  = $this->validados->get($cliente->id);

        $pagoPlan = '-';
        if ($pendiente) {
            $pagoPlan = 'POR VALIDAR - ' . optional($pendiente->plan)->nombre . ' S/ ' . number_format($pendiente->monto, 2);
        } elseif ($validado) {
            $pagoPlan = 'VALIDADO' . ($validado->fechavencimiento ? ' - Vence ' . Carbon::parse($validado->fechavencimiento)->format('d/m/Y') : '');
        }

        $situacion   = $cliente->situacion;
        $congelado   = $this->congelamientos->get($cliente->id);
        if ($congelado) {
            $situacion .= ' - CONGELADO desde ' . Carbon::parse($congelado->fechainicio)->format('d/m/Y');
        }

        // Reservas no canceladas desde el inicio del último pago validado
        $reservadas = null;
        if ($validado && $validado->fechainicio) {
            $reservadas = Reserva::where('cliente_id', '=', $cliente->id)
                ->where('fecha', '>=', $validado->fechainicio)
                ->whereNull('deleted_at')
                ->count();
        }

        return array(
            $cliente->documento,
            $cliente->apellidopaterno . ' ' . $cliente->apellidomaterno . ' ' . $cliente->nombres,
            $cliente->direccion,
            $this->fecha($cliente->fechanacimiento),
            $cliente->dniapoderado,
            $cliente->celular,
            $cliente->correo,
            optional($cliente->plan)->nombre ?? '-',
            $cliente->creditos,
            $situacion,
            $pagoPlan,
            $this->fecha($cliente->created_at),
            $validado ? $this->fecha($validado->fechapago) : null,
            $validado ? (float) $validado->monto : null,
            $reservadas,
        );
    }

    /** Fecha como valor de Excel (null si está vacía) */
    private function fecha($valor)
    {
        return $valor ? Date::dateTimeToExcel(Carbon::parse($valor)->startOfDay()) : null;
    }

    public function columnFormats(): array
    {
        return array(
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'L' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'M' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        );
    }

    public function styles(Worksheet $sheet): ?array
    {
        return array(1 => array('font' => array('bold' => true)));
    }
}
