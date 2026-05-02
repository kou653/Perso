<?php

namespace App\Services\Order;

use App\Models\CustomizationProject;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from a set of projects.
     *
     * @param User $user
     * @param array $projectIds
     * @param array $shippingAddress
     * @return Order
     * @throws \Exception
     */
    public function createFromProjects(User $user, array $projectIds, array $shippingAddress): Order
    {
        return DB::transaction(function () use ($user, $projectIds, $shippingAddress) {
            $projects = CustomizationProject::query()
                ->whereIn('id', $projectIds)
                ->where('user_id', $user->id)
                ->with('product')
                ->get();

            if ($projects->isEmpty()) {
                throw new \Exception('No valid projects found for order creation.');
            }

            $totalAmount = $projects->sum(fn($p) => $p->product->price);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_address' => $shippingAddress,
            ]);

            foreach ($projects as $project) {
                $order->items()->create([
                    'customization_project_id' => $project->id,
                    'quantity' => 1, // Default to 1
                    'unit_price' => $project->product->price,
                    'total_price' => $project->product->price,
                ]);

                // Mark project as "ordered"
                $project->update(['status' => 'ordered']);
            }

            return $order;
        });
    }
}
