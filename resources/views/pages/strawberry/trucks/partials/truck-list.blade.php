<h4 class="{{ $headingClass }} mb-2">{{ $heading }}</h4>

<div class="table-responsive w-100">
    <table class="table table-secondary table-bordered table-hover text-center align-middle">
        <thead>
            <tr class="table-dark">
                <th>#</th>
                <th>الصنف / الأصناف</th>
                <th>عدد الصناديق</th>
                <th>التاريخ</th>
                <th>السائق</th>
                <th>العميل</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($trucks as $truck)
                <tr class="{{ \App\Support\BusinessDate::isToday($truck->date) ? 'table-info' : '' }}">
                    <th scope="row">
                        @if ($hasFilters)
                            {{ transform_numbers($loop->iteration) }}
                        @else
                            {{ transform_numbers(($trucks->currentPage() - 1) * $trucks->perPage() + $loop->iteration) }}
                        @endif
                    </th>

                    <td>{{ $truck->fruit_names }}</td>

                    <td>{{ transform_numeric_value($truck->total_boxes) }}</td>

                    <td>{{ transform_numbers($truck->date->format('d-m-Y')) }}</td>

                    <td>{{ $truck->driver?->name ?? '—' }}</td>

                    <td>{{ $truck->client_names ?? 'مشتروات' }}</td>

                    <td>
                        <x-status-chip type="completed" :value="$truck->bought_boxes >= $truck->total_boxes" />
                    </td>

                    <td class="d-flex justify-content-center gap-1">
                        <a href="{{ route('strawberry-trucks.show', $truck->id) }}" class="btn btn-info btn-sm">
                            عرض <i class="bi bi-eye-fill"></i>
                        </a>

                        <a href="{{ route('strawberry-trucks.edit', $truck->id) }}" class="btn btn-warning btn-sm">
                            تعديل <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-muted text-center">
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if (!$hasFilters)
    <div class="d-flex justify-content-center mt-3">
        {{ $trucks->links() }}
    </div>
@endif
