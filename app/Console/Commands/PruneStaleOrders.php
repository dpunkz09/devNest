<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

class PruneStaleOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:prune
                            {--pending=7 : Remove pending orders that are older than this many days}
                            {--cancelled=30 : Remove cancelled orders that are older than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the orders that never turned into a sale';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pending = $this->removeOrders(OrderStatus::Pending, (int) $this->option('pending'));
        $cancelled = $this->removeOrders(OrderStatus::Cancelled, (int) $this->option('cancelled'));

        $this->info("Removed {$pending} pending and {$cancelled} cancelled orders.");

        return self::SUCCESS;
    }

    /**
     * Remove the orders of a status that are older than the given number of days.
     *
     * Orders that were paid or refunded are never removed: they are the proof
     * of a sale and the history of the seller.
     */
    protected function removeOrders(OrderStatus $status, int $days): int
    {
        if ($days < 1) {
            return 0;
        }

        return Order::query()
            ->where('status', $status)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
