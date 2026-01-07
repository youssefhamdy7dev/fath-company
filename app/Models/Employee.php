<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $guarded = [];
    protected $casts = [
        'start_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(EmployeeHoliday::class);
    }
    public function withdrawals(): HasMany
    {
        return $this->hasMany(EmployeeWithdrawal::class);
    }
    public function monthlyWages(): HasMany
    {
        return $this->hasMany(EmployeeMonthlyWage::class);
    }

    /**
     * Custom methods
     */
    public function updateStartDate($endDate): void
    {
        $this->update(['start_date' => Carbon::parse($endDate)->addDay()->format('Y-m-d')]);
    }
    public function resetHolidays(): void
    {
        $this->holidays()->delete();
    }
    public function resetWithdrawals(): void
    {
        $this->withdrawals()->delete();
    }
    public function updateEmployeeWithdrawal($overlimit, $remaining): void
    {
        $this->update([
            'over_withdrawal_limit' => $overlimit == 0 ? $overlimit : ($overlimit += $this->over_withdrawal_limit),
            'remaining_withdrawal' => $remaining == 0 ? $remaining : ($remaining += $this->remaining_withdrawal),
        ]);
    }

    /**
     * Accessors
     */
    public function getWorkingDays(Carbon $endDate): int
    {
        $start = Carbon::parse($this->start_date);
        return $start->diffInDays($endDate) + 1;
    }
    public function getHolidayCount(Carbon $endDate): int
    {
        return $this->holidays()
            ->whereBetween('date', [$this->start_date, $endDate->format('Y-m-d')])
            ->count();
    }
    public function getWithdrawalsSum(Carbon $endDate): float
    {
        return $this->withdrawals()
            ->whereBetween('date', [$this->start_date, $endDate->format('Y-m-d')])
            ->sum('amount');
    }
    public function calculateMonthlyWage(Carbon $endDate): array
    {
        $payment = $this->payment;
        $totalDays = $this->getWorkingDays($endDate);
        $holidayCount = $this->getHolidayCount($endDate);
        $withdrawSum = $this->getWithdrawalsSum($endDate);
        $workingDays = $totalDays - $holidayCount;
        // Base wage
        $wage = $totalDays * $payment;
        // Deduct holiday cost
        $holidayDeduction = $holidayCount * $payment;
        // Deduct withdrawals
        $withdrawDeduction = $withdrawSum;
        // Final salary
        $final = $wage - ($holidayDeduction + $withdrawDeduction);
        return [
            'payment'            => $payment,
            'start_date'         => Carbon::parse($this->start_date),
            'end_date'           => $endDate,
            'total_days'         => $totalDays,
            'working_days'       => $workingDays,
            'wage'               => $wage,
            'holiday_count'      => $holidayCount,
            'holiday_deduction'  => $holidayDeduction,
            'withdraw_sum'       => $withdrawSum,
            'final_salary'       => $final,
        ];
    }
}
