<?php

namespace App\Livewire;

use App\Models\Debt;
use App\Models\Lunch;
use App\Models\LunchItem;
use App\Models\LunchParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class LunchCreate extends Component
{
    public int $step = 1;

    public string $restaurantName = '';
    public string $description    = '';

    // [['user_id' => int, 'name' => string, 'items' => [['name' => string, 'price' => int]]]]
    public array    $participants    = [];
    public ?int     $selectedUserId  = null;

    public string $deliveryCostKc = '0';
    public string $splitMethod    = 'by_price'; // 'equal' | 'by_price'

    public function mount(): void
    {
        $user = auth()->user();
        $this->participants[] = [
            'user_id' => $user->id,
            'name' => $user->name . ' (Vy)',
            'items' => [],
        ];
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'restaurantName' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);
        }

        if ($this->step === 2) {
            if (count($this->participants) < 2) {
                $this->addError('participants', 'Přidejte alespoň jednoho dalšího účastníka.');
                return;
            }

            foreach ($this->participants as $i => $p) {
                if (empty($p['items'])) {
                    $this->addError('participants', 'Účastník "' . $p['name'] . '" nemá žádné položky.');
                    return;
                }
            }
        }

        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function addParticipant(): void
    {
        if (!$this->selectedUserId) return;

        $alreadyAdded = collect($this->participants)->pluck('user_id')->contains($this->selectedUserId);
        if ($alreadyAdded) {
            $this->addError('selectedUserId', 'Tento uživatel je již přidán.');
            return;
        }

        $user = User::find($this->selectedUserId);
        if (!$user) return;

        $this->participants[] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'items' => [],
        ];

        $this->selectedUserId = null;
        $this->resetErrorBag('selectedUserId');
    }

    public function removeParticipant(int $index): void
    {
        if ($index === 0) return; // organizer cannot be removed

        array_splice($this->participants, $index, 1);
        $this->participants = array_values($this->participants);
    }

    public function addItem(int $participantIndex, string $name, string $priceKc): void
    {
        $name = trim($name);

        if (!$name) {
            $this->addError("item_{$participantIndex}", 'Zadejte název položky.');
            return;
        }

        if (!is_numeric($priceKc) || (float) $priceKc <= 0) {
            $this->addError("item_{$participantIndex}", 'Zadejte platnou cenu.');
            return;
        }

        $priceHalere = (int) round((float) str_replace(',', '.', $priceKc) * 100);

        $this->participants[$participantIndex]['items'][] = [
            'name' => $name,
            'price' => $priceHalere,
        ];

        $this->resetErrorBag("item_{$participantIndex}");
    }

    public function removeItem(int $participantIndex, int $itemIndex): void
    {
        array_splice($this->participants[$participantIndex]['items'], $itemIndex, 1);
        $this->participants[$participantIndex]['items'] = array_values(
            $this->participants[$participantIndex]['items']
        );
    }

    public function getCalculatedSharesProperty(): array
    {
        $numParticipants = count($this->participants);
        if ($numParticipants === 0) return [];

        $deliveryCostHalere = (int) round((float) str_replace(',', '.', $this->deliveryCostKc) * 100);
        $deliveryPerPerson = $numParticipants > 0 ? (int) round($deliveryCostHalere / $numParticipants) : 0;

        if ($this->splitMethod === 'equal') {
            $totalItems = collect($this->participants)->sum(fn ($p) => collect($p['items'])->sum('price'));
            $grandTotal = $totalItems + $deliveryCostHalere;
            $perPerson = $numParticipants > 0 ? (int) round($grandTotal / $numParticipants) : 0;

            return collect($this->participants)->map(fn ($p) => [
                'user_id'=> $p['user_id'],
                'name' => $p['name'],
                'items_total' => collect($p['items'])->sum('price'),
                'delivery' => $deliveryPerPerson,
                'final' => $perPerson,
            ])->toArray();
        }

        return collect($this->participants)->map(fn ($p) => [
            'user_id' => $p['user_id'],
            'name' => $p['name'],
            'items_total' => collect($p['items'])->sum('price'),
            'delivery' => $deliveryPerPerson,
            'final' => collect($p['items'])->sum('price') + $deliveryPerPerson,
        ])->toArray();
    }

    public function submit(): void
    {
        $this->validate([
            'restaurantName' => 'required|string|max:255',
            'deliveryCostKc' => 'numeric|min:0',
            'splitMethod' => 'in:equal,by_price',
        ]);

        if (count($this->participants) < 2) {
            $this->addError('participants', 'Přidejte alespoň jednoho dalšího účastníka.');
            return;
        }

        $shares = $this->calculatedShares;
        $deliveryCostHalere = (int) round((float) str_replace(',', '.', $this->deliveryCostKc) * 100);
        $totalAmount = collect($shares)->sum('final');
        $organizerId = auth()->id();

        DB::transaction(function () use ($shares, $deliveryCostHalere, $totalAmount, $organizerId) {
            $lunch = Lunch::create([
                'organizer_id' => $organizerId,
                'restaurant_name' => $this->restaurantName,
                'description' => $this->description ?: null,
                'total_amount' => $totalAmount,
                'delivery_cost' => $deliveryCostHalere,
                'split_method' => $this->splitMethod,
            ]);

            foreach ($shares as $share) {
                $isOrganizer = $share['user_id'] === $organizerId;

                $participant = LunchParticipant::create([
                    'lunch_id' => $lunch->id,
                    'user_id' => $share['user_id'],
                    'amount' => $share['final'],
                    'is_approved' => $isOrganizer,
                    'approved_at' => $isOrganizer ? now() : null,
                ]);

                // persist items for this participant
                $participantData = collect($this->participants)
                    ->firstWhere('user_id', $share['user_id']);

                foreach ($participantData['items'] as $item) {
                    LunchItem::create([
                        'lunch_participant_id' => $participant->id,
                        'name' => $item['name'],
                        'price' => $item['price'],
                    ]);
                }

                // debt only for non organizer participants
                if (!$isOrganizer) {
                    Debt::create([
                        'user_id' => $share['user_id'],
                        'creditor_id' => $organizerId,
                        'lunch_id' => $lunch->id,
                        'amount' => $share['final'],
                        'is_paid' => false,
                        'is_accepted' => false,
                    ]);
                }
            }
        });

        session()->flash('success', 'Oběd byl vytvořen a účastníci byli upozorněni.');
        $this->redirect(route('lunches'), navigate: true);
    }

    public function render()
    {
        $addedIds = collect($this->participants)->pluck('user_id')->toArray();
        $availableUsers = User::where('id', '!=', auth()->id())
            ->whereNotIn('id', $addedIds)
            ->where('enabled', true)
            ->orderBy('name')
            ->get();

        return view('livewire.lunch-create', [
            'availableUsers' => $availableUsers,
            'shares' => $this->calculatedShares,
        ]);
    }
}
