<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SystemCommandController extends Controller
{
    public function updateAndOptimize(): RedirectResponse
    {
        $command = 'git pull && php artisan optimize';

        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(300);

        try {
            $process->mustRun();

            return back()->with('success', 'System updated and optimized successfully.');
        } catch (ProcessFailedException $exception) {
            $output = trim($process->getErrorOutput() ?: $process->getOutput());
            $output = $output !== '' ? mb_substr($output, 0, 1000) : $exception->getMessage();

            return back()->with('error', 'Command failed: ' . $output);
        }
    }
}
