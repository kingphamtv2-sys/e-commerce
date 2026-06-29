<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerAddressService
{
    public function create(User $user, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $data): CustomerAddress {
            $data = $this->normalizeDefaults($user, $data);

            return $user->customerAddresses()->create($data);
        });
    }

    public function update(User $user, CustomerAddress $address, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $address, $data): CustomerAddress {
            $data = $this->normalizeDefaults($user, $data, $address);
            $address->update($data);

            return $address->refresh();
        });
    }

    public function setDefault(User $user, CustomerAddress $address, string $type): CustomerAddress
    {
        return DB::transaction(function () use ($user, $address, $type): CustomerAddress {
            $column = $type === 'billing' ? 'is_default_billing' : 'is_default_shipping';
            $user->customerAddresses()->where('id', '!=', $address->id)->update([$column => false]);
            $address->forceFill([$column => true])->save();

            return $address->refresh();
        });
    }

    private function normalizeDefaults(User $user, array $data, ?CustomerAddress $current = null): array
    {
        foreach (['is_default_shipping', 'is_default_billing'] as $column) {
            $requested = (bool) ($data[$column] ?? false);
            $hasDefault = $user->customerAddresses()
                ->when($current, fn ($query) => $query->where('id', '!=', $current->id))
                ->where($column, true)
                ->exists();

            if ($requested) {
                $user->customerAddresses()
                    ->when($current, fn ($query) => $query->where('id', '!=', $current->id))
                    ->update([$column => false]);
            } elseif (! $hasDefault) {
                $data[$column] = true;
            }
        }

        return $data;
    }
}
