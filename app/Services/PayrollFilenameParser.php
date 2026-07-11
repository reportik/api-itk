<?php

namespace App\Services;

class PayrollFilenameParser
{
    /**
     * Parsea nombres como RE_0000_Semanal_2024_32_00392_EFC.pdf
     *
     * @return array{registroPatronal: string, anio: string, semana: int, empleadoCodigo: string}|null
     */
    public function parse(string $filename): ?array
    {
        $name = basename($filename);
        $parts = explode('_', $name);

        if (count($parts) < 6) {
            return null;
        }

        $employeePart = $parts[5];
        if (strpos($employeePart, '.') !== false) {
            $employeePart = substr($employeePart, 0, strpos($employeePart, '.'));
        }

        $week = (int) $parts[4];
        if ($week <= 0) {
            return null;
        }

        return [
            'registroPatronal' => $parts[0] . $parts[1],
            'anio' => $parts[3],
            'semana' => $week,
            'empleadoCodigo' => ltrim($employeePart, '0') !== '' ? ltrim($employeePart, '0') : '0',
        ];
    }

    public function normalizeEmployeeCode(string $code): string
    {
        $trimmed = trim($code);
        if ($trimmed === '') {
            return '';
        }

        return ltrim($trimmed, '0') !== '' ? ltrim($trimmed, '0') : '0';
    }

    public function employeeCodeVariants(string $code): array
    {
        $normalized = $this->normalizeEmployeeCode($code);
        if ($normalized === '') {
            return [];
        }

        return array_values(array_unique([
            $code,
            $normalized,
            str_pad($normalized, 4, '0', STR_PAD_LEFT),
            str_pad($normalized, 5, '0', STR_PAD_LEFT),
        ]));
    }
}
