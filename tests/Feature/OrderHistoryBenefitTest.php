<?php

namespace Tests\Feature;

use App\Models\History;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderHistoryBenefitTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_history_is_free_when_user_accepts_benefit(): void
    {
        [$user, $patient, $service] = $this->recentHistoryScenario();

        $response = $this->actingAs($user)->post(route('orders.store'), $this->orderPayload($patient, $service, [
            'apply_history_benefit' => '1',
        ]));

        $response->assertRedirect(route('orders.index'));
        $order = Order::latest('id')->firstOrFail();
        $this->assertEquals(0.0, (float) $order->total);
        $this->assertEquals(0.0, (float) $order->details()->firstOrFail()->price);
    }

    public function test_recent_history_is_charged_when_user_declines_benefit(): void
    {
        [$user, $patient, $service] = $this->recentHistoryScenario();

        $response = $this->actingAs($user)->post(route('orders.store'), $this->orderPayload($patient, $service));

        $response->assertRedirect(route('orders.index'));
        $order = Order::latest('id')->firstOrFail();
        $this->assertEquals(100.0, (float) $order->total);
        $this->assertEquals(100.0, (float) $order->details()->firstOrFail()->price);
    }

    public function test_history_charge_is_not_waived_after_benefit_expires(): void
    {
        Carbon::setTestNow('2026-08-06 10:30:00');

        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $service = Service::create(['nombre' => 'HISTORIA CLINICA', 'precio' => 100]);

        History::create([
            'patient_id' => $patient->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(21),
            'updated_at' => now()->subDays(21),
        ]);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'patient_id' => $patient->id,
            'payment_status' => 'pagado',
            'total_amount' => 100,
            'apply_history_benefit' => '1',
            'items' => [[
                'id' => $service->id,
                'name' => $service->nombre,
                'type' => 'service',
                'quantity' => 1,
                'unit_price' => 100,
                'price' => 100,
            ]],
        ]);

        $response->assertRedirect(route('orders.index'));

        $order = Order::latest('id')->firstOrFail();
        $this->assertEquals(100.0, (float) $order->total);
        $this->assertEquals(100.0, (float) $order->details()->firstOrFail()->price);
    }

    private function recentHistoryScenario(): array
    {
        Carbon::setTestNow('2026-08-06 10:30:00');
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $service = Service::create(['nombre' => 'HISTORIA CLINICA', 'precio' => 100]);

        History::create([
            'patient_id' => $patient->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        return [$user, $patient, $service];
    }

    private function orderPayload(Patient $patient, Service $service, array $overrides = []): array
    {
        return array_merge([
            'patient_id' => $patient->id,
            'payment_status' => 'pagado',
            'total_amount' => 100,
            'items' => [[
                'id' => $service->id,
                'name' => $service->nombre,
                'type' => 'service',
                'quantity' => 1,
                'unit_price' => 100,
                'price' => 100,
            ]],
        ], $overrides);
    }
}
