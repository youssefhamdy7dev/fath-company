<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    /**
     * Attributes available for mass assignment.
     */
    protected $guarded = [];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'start_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the employee's holidays.
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(EmployeeHoliday::class);
    }

    /**
     * Get the employee's withdrawals.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(EmployeeWithdrawal::class);
    }

    /**
     * Get the employee's monthly wages.
     */
    public function monthlyWages(): HasMany
    {
        return $this->hasMany(EmployeeMonthlyWage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Operations
    |--------------------------------------------------------------------------
    */

    /**
     * Update the employee's start date to the day after the given date.
     */
    public function updateStartDate($endDate): void
    {
        $this->update([
            'start_date' => Carbon::parse($endDate)
                ->addDay()
                ->format('Y-m-d'),
        ]);
    }

    /**
     * Delete all holidays associated with this employee.
     */
    public function resetHolidays(): void
    {
        $this->holidays()->delete();
    }

    /**
     * Delete all withdrawals associated with this employee.
     */
    public function resetWithdrawals(): void
    {
        $this->withdrawals()->delete();
    }

    /**
     * Update the employee's withdrawal limits and remaining amount.
     */
    public function updateEmployeeWithdrawal($overlimit, $remaining): void
    {
        $this->update([
            'over_withdrawal_limit' => $overlimit == 0
                ? $overlimit
                : ($overlimit += $this->over_withdrawal_limit),

            'remaining_withdrawal' => $remaining == 0
                ? $remaining
                : ($remaining += $this->remaining_withdrawal),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Calculations
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the number of working-calendar days from the start date
     * through the supplied end date, inclusive.
     */
    public function getWorkingDays(Carbon $endDate): int
    {
        $start = Carbon::parse($this->start_date);

        return $start->diffInDays($endDate) + 1;
    }

    /**
     * Count holidays between the employee's start date and end date.
     */
    public function getHolidayCount(Carbon $endDate): int
    {
        return $this->holidays()
            ->whereBetween('date', [
                $this->start_date,
                $endDate->format('Y-m-d'),
            ])
            ->count();
    }

    /**
     * Calculate the total withdrawals between the start date and end date.
     */
    public function getWithdrawalsSum(Carbon $endDate): float
    {
        return $this->withdrawals()
            ->whereBetween('date', [
                $this->start_date,
                $endDate->format('Y-m-d'),
            ])
            ->sum('amount');
    }

    /**
     * Calculate the employee's salary for the supplied end date.
     */
    public function calculateMonthlyWage(Carbon $endDate): array
    {
        $payment = $this->payment;

        $totalDays = $this->getWorkingDays($endDate);
        $holidayCount = $this->getHolidayCount($endDate);
        $withdrawSum = $this->getWithdrawalsSum($endDate);

        $workingDays = $totalDays - $holidayCount;

        // Base wage.
        $wage = $totalDays * $payment;

        // Deduct holiday cost.
        $holidayDeduction = $holidayCount * $payment;

        // Deduct withdrawals.
        $withdrawDeduction = $withdrawSum;

        // Final salary.
        $final = $wage - ($holidayDeduction + $withdrawDeduction);

        return [
            'payment' => $payment,
            'start_date' => Carbon::parse($this->start_date),
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'wage' => $wage,
            'holiday_count' => $holidayCount,
            'holiday_deduction' => $holidayDeduction,
            'withdraw_sum' => $withdrawSum,
            'final_salary' => $final,
        ];
    }
}
