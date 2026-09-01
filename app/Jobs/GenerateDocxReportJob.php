<?php

namespace App\Jobs;

use App\Models\ReportGeneration;
use App\Models\ReportTemplate;
use App\Services\ReportGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GenerateDocxReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(public int $generationId)
    {
    }

    public function handle(ReportGeneratorService $generatorService): void
    {
        $generation = ReportGeneration::findOrFail($this->generationId);
        $template = ReportTemplate::with('placeholders')->findOrFail($generation->template_id);

        $generation->update(['status' => 'processing']);

        try {
            Auth::onceUsingId($generation->generated_by);

            $outputPath = $generatorService->generateDocxReport(
                $template,
                $generation->generated_data ?? [],
                $generation->manual_inputs ?? []
            );

            $generation->update([
                'output_file_path' => $outputPath,
                'status' => 'completed',
            ]);
        } catch (\Throwable $exception) {
            $generation->update(['status' => 'failed']);
            Log::error('Queued report generation failed', [
                'generation_id' => $generation->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}