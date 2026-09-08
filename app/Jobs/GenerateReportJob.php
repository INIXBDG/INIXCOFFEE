<?php

namespace App\Jobs;

use App\Models\ReportGeneration;
use App\Services\ReportGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $generation;

    public function __construct(ReportGeneration $generation)
    {
        $this->generation = $generation;
    }

    public function handle(ReportGeneratorService $generatorService): void
    {
        Log::info("Memulai Job: GenerateReportJob untuk ReportGeneration ID {$this->generation->id}...");

        try {
            $template = $this->generation->template;
            $sourceData = $this->generation->generated_data ?? [];
            $manualInputs = $this->generation->manual_inputs ?? [];

            $outputPath = $generatorService->generateDocxReport($template, $sourceData, $manualInputs);

            $this->generation->update([
                'output_file_path' => $outputPath,
                'status' => 'completed',
            ]);

            Log::info("Job GenerateReportJob selesai untuk ReportGeneration ID {$this->generation->id}.");
        } catch (\Exception $e) {
            Log::error("Job GenerateReportJob GAGAL untuk ReportGeneration ID {$this->generation->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $this->generation->update([
                'status' => 'failed',
            ]);

            throw $e;
        }
    }
}
