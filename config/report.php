<?php

return [
    'placeholder_positions' => [
        'nama_karyawan' => ['x' => 50, 'y' => 30, 'size' => 12, 'width' => 80],
        'jabatan' => ['x' => 50, 'y' => 40, 'size' => 10, 'width' => 80],
        'tanggal_kegiatan' => ['x' => 120, 'y' => 30, 'size' => 10, 'width' => 60],
        'uraian_kegiatan' => ['x' => 20, 'y' => 80, 'size' => 9, 'width' => 170, 'multiline' => true],
    ],
    
    'default_fonts' => [
        'regular' => 'helvetica',
        'bold' => 'helveticaB',
        'italic' => 'helveticaI'
    ],
    
    'allowed_extensions' => ['pdf'],
    'max_file_size' => 10240,
];