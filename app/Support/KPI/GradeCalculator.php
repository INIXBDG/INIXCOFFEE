<?php

namespace App\Support\KPI;

class GradeCalculator
{
    private const SCALE = [
        ['min' => 100.0, 'grade' => 'A', 'label' => 'Sangat Baik',   'level' => null,       'badge' => 'success'],
        ['min' => 95.0,  'grade' => 'B', 'label' => 'Baik',          'level' => 'High',     'badge' => 'primary'],
        ['min' => 90.0,  'grade' => 'B', 'label' => 'Baik',          'level' => 'Mid',      'badge' => 'primary'],
        ['min' => 85.0,  'grade' => 'B', 'label' => 'Baik',          'level' => 'Standard', 'badge' => 'primary'],
        ['min' => 80.0,  'grade' => 'B', 'label' => 'Baik',          'level' => 'Low',      'badge' => 'primary'],
        ['min' => 70.0,  'grade' => 'C', 'label' => 'Cukup',         'level' => null,       'badge' => 'warning text-dark'],
        ['min' => 60.0,  'grade' => 'D', 'label' => 'Kurang',        'level' => null,       'badge' => 'danger'],
        ['min' => 0.0,   'grade' => 'E', 'label' => 'Sangat Kurang', 'level' => null,       'badge' => 'dark'],
    ];

    public static function fromScore($score): array
    {
        $value = round(max(0.0, min(100.0, (float) $score)), 1);
        $matched = self::SCALE[count(self::SCALE) - 1];

        foreach (self::SCALE as $row) {
            if ($value >= $row['min']) {
                $matched = $row;
                break;
            }
        }

        return [
            'grade'   => $matched['grade'],
            'label'   => $matched['label'],
            'level'   => $matched['level'],
            'display' => $matched['grade'] . ' (' . $matched['label'] . ($matched['level'] ? ' - ' . $matched['level'] : '') . ')',
            'badge'   => $matched['badge'],
            'score'   => $value,
        ];
    }
}