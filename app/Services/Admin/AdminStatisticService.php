<?php

namespace App\Services\Admin;

use App\DTOs\ResponseDTO;
use App\Enums\OrderStatusEnum;
use App\Enums\TakenScheduleStatusEnum;
use App\Enums\TutorStatusEnum;
use App\Models\Order;
use App\Models\Student;
use App\Models\TakenSchedule;
use App\Models\Tutor;
use Illuminate\Support\Carbon;

class AdminStatisticService
{
    public function summary(string $period): ResponseDTO
    {
        [$start, $end] = $this->resolvePeriod($period);

        $orderQuery = Order::query()->where('status', OrderStatusEnum::PAID->value);
        $scheduleQuery = TakenSchedule::query();

        if ($start && $end) {
            $orderQuery->whereBetween('created_at', [$start, $end]);
            $scheduleQuery->whereBetween('created_at', [$start, $end]);
        }

        // Pembelian paket
        $totalOrders    = (clone $orderQuery)->count();
        $totalRevenue   = (clone $orderQuery)->sum('total_amount');
        $totalSessions  = (clone $orderQuery)->join('orders_items', 'orders.id', '=', 'orders_items.order_id')
                            ->join('packages', 'orders_items.package_id', '=', 'packages.id')
                            ->sum('packages.session');

        // Breakdown per paket
        $packageBreakdown = (clone $orderQuery)
            ->join('orders_items', 'orders.id', '=', 'orders_items.order_id')
            ->join('packages', 'orders_items.package_id', '=', 'packages.id')
            ->selectRaw('packages.id, packages.name, SUM(orders_items.qty) as total_bought, SUM(orders_items.subtotal) as total_revenue')
            ->groupBy('packages.id', 'packages.name')
            ->get();

        // Jadwal
        $totalSchedules   = (clone $scheduleQuery)->count();
        $activeSchedules  = (clone $scheduleQuery)->where('status', TakenScheduleStatusEnum::ACTIVE->value)->count();
        $completedSchedules = (clone $scheduleQuery)->where('status', TakenScheduleStatusEnum::COMPLETED->value)->count();
        $cancelledSchedules = (clone $scheduleQuery)->whereIn('status', [
            TakenScheduleStatusEnum::CANCELLED->value,
            TakenScheduleStatusEnum::REJECTED->value,
        ])->count();

        // Student
        $totalStudents  = Student::query()->count();
        $activeStudents = TakenSchedule::query()
            ->where('status', TakenScheduleStatusEnum::ACTIVE->value)
            ->distinct('student_id')
            ->count('student_id');

        // Tutor
        $totalTutors    = Tutor::query()->count();
        $activeTutors   = Tutor::query()->where('status', TutorStatusEnum::VERIFIED->value)->count();
        $pendingTutors  = Tutor::query()->where('status', TutorStatusEnum::PENDING->value)->count();

        return new ResponseDTO('success', 'Statistik admin', [
            'period' => $period,
            'orders' => [
                'total_transactions' => $totalOrders,
                'total_revenue'      => $totalRevenue,
                'total_sessions_sold'=> $totalSessions,
                'breakdown_by_package' => $packageBreakdown,
            ],
            'schedules' => [
                'total'     => $totalSchedules,
                'active'    => $activeSchedules,
                'completed' => $completedSchedules,
                'cancelled' => $cancelledSchedules,
            ],
            'students' => [
                'total'  => $totalStudents,
                'active' => $activeStudents,
            ],
            'tutors' => [
                'total'   => $totalTutors,
                'active'  => $activeTutors,
                'pending' => $pendingTutors,
            ],
        ], null, 200);
    }

    private function resolvePeriod(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today'      => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'this_week'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'this_year'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'all_time'   => [null, null],
            default      => [null, null],
        };
    }
}
