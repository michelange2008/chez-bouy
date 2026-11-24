<?php

namespace Tests\Feature;

use App\Models\User;
use App\RememberedDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RememberedDeviceTest extends TestCase
{
    use RefreshDatabase;

    private function verifyCode(User $user, array $options = [], string $code = '123456'): TestResponse
    {
        $this->withoutVite();
        Cache::put('login-code:'.hash('sha256', $user->email), Hash::make('123456'), 600);

        return $this->withSession(['login_email' => $user->email])
            ->post('/connexion/code', ['code' => $code, ...$options]);
    }

    private function endSession(): void
    {
        Auth::forgetGuards();
        $this->app['session']->flush();
    }

    public function test_device_restores_admin_and_expires_six_months_after_code_verification(): void
    {
        $this->travelTo(now()->setDate(2026, 8, 31)->startOfDay());
        $user = User::factory()->create(['role' => 'admin']);
        $cookie = $this->verifyCode($user, ['remember_device' => '1'])->assertRedirect(route('admin'))->getCookie(RememberedDevice::COOKIE);
        $this->assertTrue($cookie->isHttpOnly());
        $this->endSession();

        $this->travelTo(now()->setDate(2027, 2, 27));
        $this->withCookie(RememberedDevice::COOKIE, $cookie->getValue())->get('/admin')->assertOk();
        $this->assertAuthenticatedAs($user);
        $this->travel(1)->days();
        $this->get('/admin')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_logout_revokes_the_device_even_if_its_cookie_is_reused(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cookie = $this->verifyCode($user, ['remember_device' => '1'])->getCookie(RememberedDevice::COOKIE);
        $this->withCookie(RememberedDevice::COOKIE, $cookie->getValue())->post('/deconnexion')->assertCookieExpired(RememberedDevice::COOKIE);
        $this->endSession();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_remembering_the_device_requires_opt_in_and_a_valid_code(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->verifyCode($user, ['remember_device' => '1'], '000000')->assertSessionHasErrors('code')->assertCookieMissing(RememberedDevice::COOKIE);
        $this->verifyCode($user)->assertRedirect(route('admin'))->assertCookieMissing(RememberedDevice::COOKIE);
        $this->endSession();
        $tenant = User::factory()->create(['role' => 'locataire']);
        $this->verifyCode($tenant)->assertRedirect(route('dashboard'))->assertCookieMissing(RememberedDevice::COOKIE);
    }

    public function test_tenant_can_opt_in_and_return_without_a_code_for_three_months(): void
    {
        $this->travelTo(now()->setDate(2026, 8, 31)->startOfDay());
        $this->withoutVite();
        $tenant = User::factory()->create(['role' => 'locataire']);
        $this->post('/connexion', ['email' => $tenant->email])->assertRedirect(route('code.form'));
        $this->get(route('code.form'))->assertOk()->assertSee(__('site.remember_tenant_device'));
        $cookie = $this->verifyCode($tenant, ['remember_device' => '1'])
            ->assertRedirect(route('dashboard'))->getCookie(RememberedDevice::COOKIE);
        $this->endSession();

        $this->get('/espace')->assertRedirect(route('login'));
        $this->travelTo(now()->setDate(2026, 11, 29));
        $this->withCookie(RememberedDevice::COOKIE, $cookie->getValue())->get('/espace')->assertOk();
        $this->assertAuthenticatedAs($tenant);
        $this->travel(1)->days();
        $this->get('/espace')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[TestWith(['locataire'])]
    #[TestWith(['admin'])]
    public function test_inactive_account_cannot_receive_or_use_a_login_code(string $role): void
    {
        Mail::shouldReceive('raw')->never();
        $user = User::factory()->create(['role' => $role, 'is_active' => false]);

        $this->post('/connexion', ['email' => $user->email])
            ->assertRedirect(route('code.form'))->assertSessionMissing('remember_device_role');
        $this->verifyCode($user, ['remember_device' => '1'])
            ->assertSessionHasErrors('code')->assertCookieMissing(RememberedDevice::COOKIE);
        $this->assertGuest();
    }

    #[TestWith([['is_active' => false]])]
    #[TestWith([['role' => 'admin']])]
    public function test_remembered_tenant_cannot_return_after_deactivation_or_role_change(array $change): void
    {
        $tenant = User::factory()->create(['role' => 'locataire']);
        $cookie = $this->verifyCode($tenant, ['remember_device' => '1'])->getCookie(RememberedDevice::COOKIE);
        $this->endSession();
        $tenant->forceFill($change)->save();

        $this->withCookie(RememberedDevice::COOKIE, $cookie->getValue())
            ->get('/espace')->assertRedirect(route('login'))->assertCookieExpired(RememberedDevice::COOKIE);
        $this->assertGuest();
    }

    public function test_only_currently_active_admins_can_return_with_their_cookie(): void
    {
        foreach ([['role' => 'locataire'], ['is_active' => false]] as $change) {
            $user = User::factory()->create(['role' => 'admin']);
            $cookie = $this->verifyCode($user, ['remember_device' => '1'])->getCookie(RememberedDevice::COOKIE);
            $this->endSession();
            $user->forceFill($change)->save();
            $this->withCookie(RememberedDevice::COOKIE, $cookie->getValue())->get('/admin')->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }
}
