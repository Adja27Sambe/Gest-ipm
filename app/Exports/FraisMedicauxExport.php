<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FraisMedicauxExport implements FromView, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        // $this->data['view_content'] contiendra par exemple 'frais-medicaux.excel.global'
        return view($this->data['view_content'], $this->data);
    }
}
