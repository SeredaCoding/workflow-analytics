<?php

namespace App\Jobs;

use App\Models\ProblemReport;
use App\Services\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReportImages implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue, Dispatchable;

    public int $tries = 3;

    protected int $reportId;

    public function __construct(int $reportId)
    {
        $this->reportId = $reportId;
    }

    public function handle(ImageService $imageService): void
    {
        $report = ProblemReport::find($this->reportId);

        if (!$report || !$report->images) {
            return;
        }

        $images = $report->images;
        $updated = [];

        foreach ($images as $index => $image) {
            $path = $image["path"] ?? null;
            if (!$path) {
                continue;
            }

            try {
                $optimized = $imageService->optimize($path, ["quality" => 80]);
                $updated[] = [
                    "path" => $optimized,
                    "primary" => $image["primary"] ?? ($index === 0),
                ];
            } catch (\Exception $e) {
                $updated[] = $image;
            }
        }

        $report->images = $updated;
        $report->save();
    }
}
