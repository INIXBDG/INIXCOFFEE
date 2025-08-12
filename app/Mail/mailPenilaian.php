<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class mailPenilaian extends Mailable
{
    use Queueable, SerializesModels;

    public $dataAbsen;
    public $evaluated;
    public $evaluator;
    public $dataKriteria;

    public function __construct($data)
    {
        $this->evaluated     = $data['evaluated'];
        $this->dataAbsen     = $data['dataAbsen'];
        $this->evaluator     = $data['evaluator'];
        $this->dataKriteria  = $data['dataKriteria'];
    }

    public function build()
    {
        return $this->subject('Hasil Penilaian Karyawan')
                    ->view('emails.kirimPenilaian')
                    ->with([
                        'evaluated'     => $this->evaluated,
                        'dataAbsen'     => $this->dataAbsen,
                        'evaluator'     => $this->evaluator,
                        'dataKriteria'  => $this->dataKriteria,
                    ]);
    }
}
