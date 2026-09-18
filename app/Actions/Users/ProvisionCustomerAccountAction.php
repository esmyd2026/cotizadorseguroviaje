<?php

namespace App\Actions\Users;

use App\Enums\UserRole;
use App\Models\Insured;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Auto-provisions a login account after a successful purchase. Account
 * persistence is allowed to fail the purchase transaction; only notification
 * delivery is best-effort and never prevents an approved payment from saving.
 *
 * The account's username and initial password are both the traveler's
 * identity document number — a deliberate business decision (shown to the
 * customer on the confirmation screen after contracting), not a generated
 * secret. The customer can change the password later via the existing
 * "forgot password" flow.
 */
class ProvisionCustomerAccountAction
{
    public function execute(Insured $insured, string $email, string $name): User
    {
        $user = User::firstOrNew(['insured_id' => $insured->id]);
        $isNewAccount = ! $user->exists;

        $user->name = $name;
        $user->email = $email;

        if ($isNewAccount) {
            $user->username = $this->uniqueUsername($insured->document_id);
            $user->password = Hash::make($insured->document_id);
            $user->role = UserRole::Customer;
            $user->insured_id = $insured->id;
        }

        $user->save();

        return $user;
    }

    /**
     * Two different insureds could coincidentally share the same document_id
     * string across document types (a cedula and a passport both "12345678",
     * for example) — usernames are unique for every account regardless of
     * type, so a rare collision falls back to a disambiguated variant instead
     * of failing account provisioning outright.
     */
    private function uniqueUsername(string $documentId): string
    {
        if (! User::where('username', $documentId)->exists()) {
            return $documentId;
        }

        $suffix = 2;

        while (User::where('username', "{$documentId}-{$suffix}")->exists()) {
            $suffix++;
        }

        return "{$documentId}-{$suffix}";
    }
}
