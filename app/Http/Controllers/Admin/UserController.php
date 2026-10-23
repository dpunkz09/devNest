<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * List every account, with optional search and filters.
     */
    public function index(Request $request): Response
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = (string) $request->input('search');

                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->when($request->filled('admin'), function ($query) use ($request): void {
                $query->where('is_admin', filter_var($request->input('admin'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->filled('verified'), function ($query) use ($request): void {
                $verified = filter_var($request->input('verified'), FILTER_VALIDATE_BOOLEAN);

                $verified
                    ? $query->whereNotNull('email_verified_at')
                    : $query->whereNull('email_verified_at');
            })
            ->when($request->filled('suspended'), function ($query) use ($request): void {
                $suspended = filter_var($request->input('suspended'), FILTER_VALIDATE_BOOLEAN);

                $suspended
                    ? $query->whereNotNull('suspended_at')
                    : $query->whereNull('suspended_at');
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => UserResource::collection($users),
            'filters' => [
                'search' => $request->input('search'),
                'admin' => $request->input('admin'),
                'verified' => $request->input('verified'),
                'suspended' => $request->input('suspended'),
            ],
        ]);
    }

    /**
     * Review a single account and its history.
     */
    public function show(User $user): Response
    {
        $user->loadCount(['products', 'orders', 'licenses']);

        return Inertia::render('Admin/Users/Show', [
            'user' => new UserResource($user),
            'stats' => [
                'products' => $user->products_count,
                'orders' => $user->orders_count,
                'licenses' => $user->licenses_count,
                'sales' => (int) $user->products()->withTrashed()->sum('sales'),
            ],
        ]);
    }

    /**
     * Promote or demote an account.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);

        if ($user->id === $request->user()->id && ! $validated['is_admin']) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'You cannot remove your own administrator access.']);

            return back();
        }

        $user->update(['is_admin' => $validated['is_admin']]);

        AuditLog::record('user.admin_toggled', $user, ['is_admin' => $validated['is_admin']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The account was updated.']);

        return back();
    }

    /**
     * Verify the email address of an account.
     */
    public function verify(User $user): RedirectResponse
    {
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        AuditLog::record('user.email_verified', $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The email address was marked as verified.']);

        return back();
    }

    /**
     * Remove the verification from an email address.
     */
    public function unverify(User $user): RedirectResponse
    {
        $user->forceFill(['email_verified_at' => null])->save();

        AuditLog::record('user.email_unverified', $user);

        Inertia::flash('toast', ['type' => 'info', 'message' => 'The email address was marked as unverified.']);

        return back();
    }

    /**
     * Send a password reset link to the account.
     */
    public function sendResetLink(User $user): RedirectResponse
    {
        PasswordFacade::sendResetLink(['email' => $user->email]);

        AuditLog::record('user.password_reset_sent', $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'A password reset link was sent.']);

        return back();
    }

    /**
     * Clear the two factor credentials of an account.
     */
    public function resetTwoFactor(User $user): RedirectResponse
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        AuditLog::record('user.two_factor_reset', $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Two factor authentication was reset.']);

        return back();
    }

    /**
     * Suspend an account.
     */
    public function suspend(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'You cannot suspend your own account.']);

            return back();
        }

        $user->forceFill(['suspended_at' => now()])->save();

        AuditLog::record('user.suspended', $user);

        Inertia::flash('toast', ['type' => 'info', 'message' => 'The account was suspended.']);

        return back();
    }

    /**
     * Lift the suspension of an account.
     */
    public function unsuspend(User $user): RedirectResponse
    {
        $user->forceFill(['suspended_at' => null])->save();

        AuditLog::record('user.unsuspended', $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The suspension was lifted.']);

        return back();
    }

    /**
     * Close an account. Accounts with history are archived, the others deleted.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'You cannot delete your own account from here.']);

            return back();
        }

        if ($user->hasOrderHistory()) {
            $user->archive();

            AuditLog::record('user.archived', $user);

            Inertia::flash('toast', ['type' => 'info', 'message' => 'The account was archived to keep its sales history.']);
        } else {
            AuditLog::record('user.deleted', $user);

            $user->delete();

            Inertia::flash('toast', ['type' => 'success', 'message' => 'The account was deleted.']);
        }

        return to_route('admin.users.index');
    }
}
