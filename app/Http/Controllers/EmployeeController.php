<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\Employee;
use App\Models\EmployeeHoliday;
use App\Models\EmployeeWithdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display all employees.
     */
    public function index()
    {
        $employees = Employee::orderBy('name')->get();

        return view('pages.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating an employee.
     */
    public function create()
    {
        return view('pages.employees.create');
    }

    /**
     * Store a new employee.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());

        return redirect()
            ->route('employees.index')
            ->with('success', "تم إضافة الموظف: {$employee->name} بنجاح.");
    }

    /**
     * Display the specified employee and related records.
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'holidays',
            'withdrawals',
            'monthlyWages' => fn($query) => $query->latest('end_date'),
        ]);

        return view('pages.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing an employee.
     */
    public function edit(Employee $employee)
    {
        return view('pages.employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()
            ->route('employees.index')
            ->with('success', "تم تحديث بيانات الموظف: {$employee->name} بنجاح.");
    }

    /**
     * Delete the specified employee and related records.
     */
    public function destroy(Employee $employee)
    {
        $employee->holidays()->delete();
        $employee->withdrawals()->delete();
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', "تم حذف الموظف: {$employee->name} بنجاح.");
    }

    /**
     * Add a holiday for an employee.
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

    /**
     * Delete an employee holiday.
     */
    public function holidayDelete(EmployeeHoliday $holiday)
    {
        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الأجازة بنجاح.',
        ]);
    }

    /**
     * Record a withdrawal for an employee.
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

    /**
     * Delete an employee withdrawal.
     */
    public function deleteWithdrawal(EmployeeWithdrawal $withdrawal)
    {
        $withdrawal->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصرف بنجاح.',
        ]);
    }

    /**
     * Preview the monthly wage for an employee.
     */
    public function previewMonthlyWage(Employee $employee, Request $request)
    {
        $request->validate([
            'end_date' => ['required', 'date'],
        ], [
            'end_date.required' => 'يرجى إدخال تاريخ آخر يوم عمل.',
        ]);

        $endDate = Carbon::parse($request->end_date);
        $data = $employee->calculateMonthlyWage($endDate);

        return back()->with([
            'wage_preview' => $data,
            'show_wage_modal' => true,
        ]);
    }

    /**
     * Store the monthly wage settlement for an employee.
     */
    public function storeMonthlyWage(Employee $employee, Request $request)
    {
        $request->validate([
            'end_date' => ['required', 'date'],
        ], [
            'end_date.required' => 'يرجى إدخال تاريخ آخر يوم عمل.',
        ]);

        $endDate = Carbon::parse($request->end_date);

        // Check whether a settlement already exists for this date.
        $conflict = $employee->monthlyWages()
            ->whereDate('end_date', $endDate)
            ->exists();

        if ($conflict) {
            return back()->with(
                'error',
                'لا يمكن حفظ خالص الحساب لأن هذا التاريخ مستخدم مسبقًا لهذا الموظف.'
            );
        }

        $data = $employee->calculateMonthlyWage($endDate);

        DB::transaction(function () use ($employee, $data) {
            $employee->monthlyWages()->create([
                'employee_start_date' => $employee->start_date,
                'end_date' => $data['end_date'],
                'total_wage' => $data['final_salary'],
            ]);

            // Move the employee start date to the day after settlement.
            $employee->updateStartDate($data['end_date']);

            // Update overlimit and remaining withdrawals.
            $employee->updateEmployeeWithdrawal(
                max(0, - ($data['final_salary'] + $employee->remaining_withdrawal)),
                max(0, $data['final_salary'] - $employee->over_withdrawal_limit)
            );

            $employee->resetHolidays();
            $employee->resetWithdrawals();
        });

        return back()->with('success', 'تم تسجيل خالص الحساب للموظف بنجاح.');
    }

    /**
     * Clear all monthly wage history for an employee.
     */
    public function clearWagesHistory(Employee $employee)
    {
        $employee->monthlyWages()->delete();

        return back()->with('success', 'تم مسح جميع تصفيات الحساب للموظف بنجاح.');
    }
}
