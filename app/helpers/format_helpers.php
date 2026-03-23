<?php

function formatKategoriName(?string $name): string
{
    if (empty($name)) {
        return '-';
    }
    
    $mappings = [
        'ALAT_TEKNIK_ELEKTRO' => 'Teknik Elektro',
        'ALAT_TEKNIK_SIPIL' => 'Teknik Sipil',
        'ALAT_TEKNOLOGI_INFORMASI' => 'Teknologi Informasi',
        'ALAT_TEKNIK_LINGKUNGAN' => 'Teknik Lingkungan',
        'ALAT_MULTIMEDIA' => 'Multimedia',
        'TEKNIK_ELEKTRO' => 'Teknik Elektro',
        'TEKNIK_SIPIL' => 'Teknik Sipil',
        'TEKNOLOGI_INFORMASI' => 'Teknologi Informasi',
        'TEKNIK_LINGKUNGAN' => 'Teknik Lingkungan',
        'MULTIMEDIA' => 'Multimedia',
    ];

    if (isset($mappings[$name])) {
        return $mappings[$name];
    }
    
    $cleaned = str_replace('ALAT_', '', $name);
    $formatted = str_replace('_', ' ', $cleaned);
    return ucwords(strtolower($formatted));
}

function formatTipeName(?string $name): string
{
    if (empty($name)) {
        return '-';
    }
    
    return ucwords(strtolower($name));
}

function formatStatus(?string $status): string
{
    if (empty($status)) {
        return 'Tidak Diketahui';
    }
    
    $mappings = [
        'TERSEDIA' => 'Tersedia',
        'DIPINJAM' => 'Dipinjam',
        'MAINTENANCE' => 'Maintenance',
        'RUSAK' => 'Rusak',
        'PENDING' => 'Menunggu Persetujuan',
        'DITOLAK' => 'Ditolak',
        'SELESAI' => 'Selesai',
    ];
    
    return $mappings[strtoupper($status)] ?? ucfirst(strtolower($status));
}

function getStatusBadgeColor(?string $status): string
{
    if (empty($status)) {
        return 'secondary';
    }
    
    $colors = [
        'TERSEDIA' => 'success',
        'DIPINJAM' => 'warning',
        'MAINTENANCE' => 'info',
        'RUSAK' => 'danger',
        'PENDING' => 'warning',
        'DITOLAK' => 'danger',
        'SELESAI' => 'success',
    ];
    
    return $colors[strtoupper($status)] ?? 'secondary';
}

function getKondisiBadgeColor(?string $kondisi): string
{
    if (empty($kondisi)) {
        return 'secondary';
    }
    
    $colors = [
        'BAIK' => 'success',
        'RUSAK_RINGAN' => 'warning',
        'RUSAK_BERAT' => 'danger',
    ];
    
    return $colors[strtoupper($kondisi)] ?? 'secondary';
}

function formatKondisi(?string $kondisi): string
{
    if (empty($kondisi)) {
        return '-';
    }
    
    $mappings = [
        'BAIK' => 'Baik',
        'RUSAK_RINGAN' => 'Rusak Ringan',
        'RUSAK_BERAT' => 'Rusak Berat',
    ];
    
    return $mappings[strtoupper($kondisi)] ?? ucfirst(strtolower(str_replace('_', ' ', $kondisi)));
}