@php $currentLocation = null; @endphp
@forelse($customers as $customer)
    @if ($currentLocation !== $customer->location)
        <tr class="table-info">
            <td colspan="6" class="text-center fw-bold">{{ $customer->location }}</td>
        </tr>
        @php $currentLocation = $customer->location; @endphp
    @endif
    <tr>
        <th scope="row">{{ transform_numeric_value($loop->iteration) }}</th>
        <td class="fw-bolder fs-5 {{ $customer->current_balance > 0 ? 'text-danger' : 'text-muted' }}">
            {{ $customer->current_balance > 0 ? transform_numeric_value($customer->current_balance) : '-' }}
        </td>
        <td>{{ $customer->name }}</td>
        <td class="{{ $customer->phone == null ? 'text-muted' : '' }}">
            {{ $customer->phone ?? 'لم يتم تسجيل رقم هاتف' }}
        </td>
        <td>{{ $customer->location }}</td>
        <td class="d-flex justify-content-center gap-1">
            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-info btn-sm">
                عرض <i class="bi bi-eye-fill"></i>
            </a>
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning btn-sm">
                تعديل <i class="bi bi-pencil-square"></i>
            </a>
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                data-bs-target="#deleteCustomerModal{{ $customer->id }}">
                حذف<i class="bi bi-trash"></i>
            </button>
            <form id="deleteCustomerForm{{ $customer->id }}" action="{{ route('customers.destroy', $customer->id) }}"
                method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="fw-bolder fs-5 text-muted">لا يوجد</td>
    </tr>
@endforelse
