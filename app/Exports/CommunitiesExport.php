<?php
// app/Exports/CommunitiesExport.php

namespace App\Exports;

use App\Models\Community;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CommunitiesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Community::with(['creator', 'members', 'moderators.user'])->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Type',
            'Total Members',
            'Created By',
            'Created Date',
            'Moderators'
        ];
    }

    public function map($community): array
    {
        $moderatorNames = $community->moderators->map(function ($moderator) {
            return $moderator->user->first_name . ' ' . $moderator->user->last_name;
        })->implode(', ');

        return [
            $community->name,
            ucfirst($community->type),
            $community->members->count(),
            $community->creator ? $community->creator->first_name . ' ' . $community->creator->last_name : 'N/A',
            $community->created_at->format('M d, Y'),
            $moderatorNames ?: 'No moderators'
        ];
    }
}