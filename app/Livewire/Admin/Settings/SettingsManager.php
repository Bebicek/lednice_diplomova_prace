<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('layouts.app')]
class SettingsManager extends Component
{
    #[Rule('nullable|string|max:34')]
    public string $bank_account_number = '';

    #[Rule('nullable|string|max:10')]
    public string $bank_code = '';

    #[Rule('nullable|string|max:100')]
    public string $bank_name = '';

    #[Rule('nullable|string|max:100')]
    public string $company_name = '';

    #[Rule('required|string|max:3')]
    public string $default_currency = 'CZK';

    #[Rule('required|integer|min:0|max:999')]
    public string $low_stock_threshold = '3';

    #[Rule('required|integer|min:0|max:365')]
    public string $expiry_warning_days = '7';

    #[Rule('boolean')]
    public bool $auto_emails_enabled = false;

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key');

        $this->bank_account_number = $settings->get('bank_account_number', '');
        $this->bank_code = $settings->get('bank_code', '');
        $this->bank_name = $settings->get('bank_name', '');
        $this->company_name = $settings->get('company_name', '');
        $this->default_currency = $settings->get('default_currency', 'CZK');
        $this->low_stock_threshold = $settings->get('low_stock_threshold', '3');
        $this->expiry_warning_days = $settings->get('expiry_warning_days', '7');
        $this->auto_emails_enabled = (bool) $settings->get('auto_emails_enabled', false);
    }

    public function save(): void
    {
        $this->validate();

        Setting::updateOrCreate(['key' => 'bank_account_number'], ['value' => $this->bank_account_number]);
        Setting::updateOrCreate(['key' => 'bank_code'], ['value' => $this->bank_code]);
        Setting::updateOrCreate(['key' => 'bank_name'], ['value' => $this->bank_name]);
        Setting::updateOrCreate(['key' => 'company_name'], ['value' => $this->company_name]);
        Setting::updateOrCreate(['key' => 'default_currency'], ['value' => strtoupper($this->default_currency)]);
        Setting::updateOrCreate(['key' => 'low_stock_threshold'], ['value' => $this->low_stock_threshold]);
        Setting::updateOrCreate(['key' => 'expiry_warning_days'], ['value' => $this->expiry_warning_days]);
        Setting::updateOrCreate(['key' => 'auto_emails_enabled'], ['value' => $this->auto_emails_enabled ? '1' : '0']);

        $this->dispatch('toast-success', message: 'Nastavení bylo úspěšně uloženo.');
    }

    public function render()
    {
        return view('livewire.admin.settings.settings-manager');
    }
}
