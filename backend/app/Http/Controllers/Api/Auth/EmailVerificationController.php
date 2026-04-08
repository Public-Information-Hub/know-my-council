<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $id, string $hash): RedirectResponse|JsonResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill([
                'verification_level' => 'verified',
                'account_state' => $user->account_state === 'suspended' ? 'suspended' : 'active',
                'email_verified_at' => Carbon::now(),
            ])->save();

            event(new Verified($user));
        }

        $frontendUrl = rtrim((string) config('knowmycouncil.frontend_url'), '/');

        return redirect()->away("{$frontendUrl}/verify-email?verified=1");
    }
}
