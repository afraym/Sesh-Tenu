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
            'message' => 'Document generation failed.',
        ], now()->addMinutes(15));
    }
}
