<?php

namespace App\Livewire;

use App\Models\Karyawan;
use Livewire\Component;

class HadirController extends Component
{
    public ?Karyawan $karyawan = null;

    protected $listeners = ['showHadirModal'];

    public function showHadirModal($karyawanNama)
    {
        $this->karyawan = Karyawan::find($karyawanNama);
        $this->dispatch('openModal', id: 'hadir-modal');
    }
    public function render()
    {
        return view('livewire.hadir-controller');
    }
}
