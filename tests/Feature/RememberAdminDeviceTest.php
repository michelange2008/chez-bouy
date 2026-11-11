<?php

namespace Tests\Feature;

use App\AdminDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RememberAdminDeviceTest extends TestCase
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
        $cookie = $this->verifyCode($user, ['remember_device' => '1'])->assertRedirect(route('admin'))->getCookie(AdminDevice::COOKIE);
        $this->assertTrue($cookie->isHttpOnly());
        $this->endSession();

        $this->travelTo(now()->setDate(2027, 2, 27));
        $this->withCookie(AdminDevice::COOKIE, $cookie->getValue())->get('/admin')->assertOk();
        $this->assertAuthenticatedAs($user);
        $this->travel(1)->days();
        $this->get('/admin')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_logout_revokes_the_device_even_if_its_cookie_is_reused(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cookie = $this->verifyCode($user, ['remember_device' => '1'])->getCookie(AdminDevice::COOKIE);
        $this->withCookie(AdminDevice::COOKIE, $cookie->getValue())->post('/deconnexion')->assertCookieExpired(AdminDevice::COOKIE);
        $this->endSession();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_only_an_admin_who_opts_in_with_a_valid_code_receives_a_cookie(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->verifyCode($user, ['remember_device' => '1'], '000000')->assertSessionHasErrors('code')->assertCookieMissing(AdminDevice::COOKIE);
        $this->verifyCode($user)->assertRedirect(route('admin'))->assertCookieMissing(AdminDevice::COOKIE);
        $this->endSession();
        $tenant = User::factory()->create(['role' => 'locataire']);
        $this->verifyCode($tenant, ['remember_device' => '1'])->assertRedirect(route('dashboard'))->assertCookieMissing(AdminDevice::COOKIE);
    }

    public function test_only_currently_active_admins_can_return_with_their_cookie(): void
    {
        foreach ([['role' => 'locataire'], ['is_active' => false]] as $change) {
            $user = User::factory()->create(['role' => 'admin']);
            $cookie = $this->verifyCode($user, ['remember_device' => '1'])->getCookie(AdminDevice::COOKIE);
            $this->endSession();
            $user->forceFill($change)->save();
            $this->withCookie(AdminDevice::COOKIE, $cookie->getValue())->get('/admin')->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }
}
