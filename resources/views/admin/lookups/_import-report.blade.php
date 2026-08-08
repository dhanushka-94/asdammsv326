@if (session('import_report'))
    @php $report = session('import_report'); @endphp
    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-ink">Import report</h2>
            <p class="mt-1 text-sm text-muted">
                {{ $report['imported'] }} saved ·
                {{ $report['failed'] }} failed
            </p>
        </div>
        @if (! empty($report['errors']))
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['errors'] as $error)
                            <tr>
                                <td>{{ $error['row'] }}</td>
                                <td>{{ $error['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="px-4 py-6 text-sm text-muted sm:px-6">All rows imported successfully.</p>
        @endif
    </div>
@endif
