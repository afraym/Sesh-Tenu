@extends('layouts.back')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Project Details / تفاصيل المشروع</h4>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">
                        <i class="tim-icons icon-minimal-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 220px;">Project Name / اسم المشروع</th>
                                    <td>{{ $project->name }}</td>
                                </tr>
                                <tr>
                                    <th>Short Name / الاسم المختصر</th>
                                    <td>{{ $project->short_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Company / الشركة</th>
                                    <td>{{ optional($project->company)->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ optional($project->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ optional($project->updated_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning btn-sm">
                            <i class="tim-icons icon-pencil"></i> Edit
                        </a>
                        <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="tim-icons icon-trash-simple"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
