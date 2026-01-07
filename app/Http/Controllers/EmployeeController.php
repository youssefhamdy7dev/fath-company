<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayRequest;
use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\EmployeeHoliday;
use App\Models\EmployeeWithdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::orderBy('name')->get();
        return view('pages.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());
        return redirect()->route('employees.index')
            ->with('success', "تم إضافة الموظف: {$employee->name} بنجاح.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'holidays',
            'withdrawals',
            'monthlyWages' => fn($q) => $q->latest('end_date')
        ]);
        return view('pages.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('pages.employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()->route('employees.index')
            ->with('success', "تم تحديث بيانات الموظف: {$employee->name} بنجاح.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->holidays()->delete();
        $employee->withdrawals()->delete();
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', "تم حذف الموظف: {$employee->name} بنجاح.");
    }

    /**
     * Manage holiday feature for the employees
     */
    public function holidayAdd(Employee $employee, HolidayRequest $request)
    {
        $holiday = $employee->holidays()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "تم إضافة أجازة للموظف: {$employee->name}",
            'holiday' => [
                'id' => $holiday->id,
                'date' => transform_numbers($holiday->date->format('d-m-Y')),
                'notes' => $holiday->notes,
                'delete_url' => route('employees.holidayDelete', $holiday->id),
            ],
        ]);
    }
    public function holidayDelete(EmployeeHoliday $holiday)
    {
        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الأجازة بنجاح.'
        ]);
    }

    /**
     * Manage withdrawals for the employees
     */
    public function withdraw(Employee $employee, WithdrawRequest $request)
    {
        $withdrawal = $employee->withdrawals()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "تم تسجيل سحب للموظف: {$employee->name}",
            'withdrawal' => [
                'id' => $withdrawal->id,
                'amount' => transform_numeric_value($withdrawal->amount),
                'date' => transform_numbers($withdrawal->date->format('d-m-Y')),
                'notes' => $withdrawal->notes,
                'delete_url' => route('employees.deleteWithdrawal', $withdrawal->id),
            ],
        ]);
    }
    public function deleteWithdrawal(EmployeeWithdrawal $withdrawal)
    {
        $withdrawal->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصرف بنجاح.'
        ]);
    }

    /**
     * Monthly wage preview for employee.
     */
    public function previewMonthlyWage(Employee $employee, Request $request)
    {
        $request->validate([
            'end_date' => ['required', 'date'],
        ], ['end_date.required' => 'يرجى إدخال تاريخ آخر يوم عمل.']);
        $endDate = Carbon::parse($request->end_date);
        $data = $employee->calculateMonthlyWage($endDate);
        return back()->with([
            'wage_preview' => $data,
            'show_wage_modal' => true,
        ]);
    }

    /**
     * Store monthly wage for employee.
     */
    public function storeMonthlyWage(Employee $employee, Request $request)
    {
        $request->validate([
            'end_date' => ['required', 'date'],
        ], ['end_date.required' => 'يرجى إدخال تاريخ آخر يوم عمل.']);

        $endDate = Carbon::parse($request->end_date);

        // Check unique month/year
        $conflict = $employee->monthlyWages()
            ->whereDate('end_date', $endDate)
            ->exists();

        if ($conflict) {
            return back()->with('error', 'لا يمكن حفظ خالص الحساب لأن هذا التاريخ مستخدم مسبقًا لهذا الموظف.');
        }

        $data = $employee->calculateMonthlyWage($endDate);

        DB::transaction(function () use ($employee, $data) {
            $employee->monthlyWages()->create([
                'employee_start_date' => $employee->start_date,
                'end_date'   => $data['end_date'],
                'total_wage' => $data['final_salary'],
            ]);
            $employee->updateStartDate($data['end_date']); // change start date to next day after end_date
            // update overlimit and remaining withdrawals
            $employee->updateEmployeeWithdrawal(
                max(0, - ($data['final_salary'] + $employee->remaining_withdrawal)),
                max(0, $data['final_salary'] + $employee->over_withdrawal_limit)
            );
            $employee->resetHolidays(); // reset holidays after wage save calculation
            $employee->resetWithdrawals(); // reset withdrawals after wage save calculation
        });

        return back()->with('success', 'تم تسجيل خالص الحساب للموظف بنجاح.');
    }
    /**
     * Clear all monthly wages for employee.
     */
    public function clearWagesHistory(Employee $employee)
    {
        $employee->monthlyWages()->delete(); // delete all monthly wages
        return back()->with('success', 'تم مسح جميع تصفيات الحساب للموظف بنجاح.');
    }
}
