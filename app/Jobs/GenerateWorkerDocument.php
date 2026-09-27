<?php

namespace App\Jobs;

use App\Http\Controllers\WorkerDocumentController;
use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateWorkerDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;

    public function __construct(
        private string $action,
        private string $token,
        private array $query,
        private ?int $workerId = null,
    ) {
    }

    public function handle(): void
    {
        $request = Request::create('/', 'GET', $this->query);
        $request->headers->set('X-Worker-Document-Job', '1');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');

        $controller = app(WorkerDocumentController::class);
        $worker = $this->workerId ? Worker::findOrFail($this->workerId) : null;

        $response = match ($this->action) {
            'exportPdf', 'exportWord', 'exportWordPdf', 'exportDailyEquipmentInspection'
                => $controller->{$this->action}($request, $worker),
            default => $controller->{$this->action}($request),
        };

        $payload = json_decode($response->getContent(), true);
        $document = $payload['document'] ?? null;

        if (! is_array($document) || ! isset($document['path'], $document['name'], $document['mime'])) {
            throw new \RuntimeException('The document generator did not return a valid file.');
        }

        $sourcePath = $document['path'];
        if (! is_file($sourcePath)) {
            $sourcePath = Storage::disk('local')->path(ltrim(str_replace('\\', '/', $sourcePath), '/'));
        }

        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            report(new \RuntimeException('Generated document source is unavailable: ' . $sourcePath));
            throw new \RuntimeException('The generated document is not readable.');
        }

        $extension = pathinfo($document['name'], PATHINFO_EXTENSION) ?: 'bin';
        $storedPath = 'generated-documents/' . $this->token . '.' . $extension;

        $targetDir = Storage::disk('local')->path('generated-documents');
        if (! is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        @chmod($targetDir, 0777);

        $storedFullPath = Storage::disk('local')->path($storedPath);

        // Try direct copy first to avoid large memory usage, fallback to stream/file_get_contents
        $copied = @copy($sourcePath, $storedFullPath);
        if (! $copied) {
            $contents = @file_get_contents($sourcePath);
            if ($contents === false || @file_put_contents($storedFullPath, $contents) === false) {
                $lastError = error_get_last();
                report(new \RuntimeException('Failed to store generated document to ' . $storedFullPath . ': ' . ($lastError['message'] ?? 'unknown error')));
                throw new \RuntimeException('The generated document could not be stored.');
            }
        }

        @chmod($storedFullPath, 0666);

        if (! file_exists($storedFullPath) || filesize($storedFullPath) === 0) {
            throw new \RuntimeException('The stored document could not be verified.');
        }

        // Clean up temporary source file and its directory if inside workers-export or temp
        if ($sourcePath !== $storedFullPath && file_exists($sourcePath)) {
            @unlink($sourcePath);
            $parentDir = dirname($sourcePath);
            if (basename(dirname($parentDir)) === 'workers-export') {
                @rmdir($parentDir);
            }
        }

        $document['path'] = $storedPath;
        Cache::put('workers.generated-document.' . $this->token, $document, now()->addMinutes(15));
        Cache::put('workers.generated-document.status.' . $this->token, [
            'status' => 'ready',
            'download_url' => route('workers.documents.download', ['token' => $this->token], false),
            'filename' => $document['name'],
        ], now()->addMinutes(15));
    }

    public function failed(Throwable $exception): void
    {
        Cache::put('workers.generated-document.status.' . $this->token, [
            'status' => 'failed',
            'message' => 'Document generation failed: ' . $exception->getMessage(),
        ], now()->addMinutes(15));
    }
}
