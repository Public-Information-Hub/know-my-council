<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class CorrectionRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_correction_request_can_be_submitted_with_the_csrf_cookie_bootstrap(): void
    {
        $xsrfToken = $this->bootstrapXsrfToken();

        $response = $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/contact/correction-request', [
                'topic' => 'correction',
                'name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'council_name' => 'Westminster City Council',
                'council_slug' => 'westminster',
                'page_url' => 'https://knowmycouncil.uk/councils/westminster',
                'source_url' => 'https://example.com/source',
                'details' => 'The page says the wrong ward boundary. Please review the source and correct it.',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('request.status', 'pending');

        $this->assertDatabaseHas('correction_requests', [
            'topic' => 'correction',
            'email' => 'ada@example.com',
            'council_slug' => 'westminster',
            'status' => 'pending',
        ]);

        $this->assertSame(1, CorrectionRequest::query()->count());
    }

    public function test_admin_summary_includes_correction_requests(): void
    {
        $superadmin = User::factory()->create([
            'is_super_admin' => true,
            'password' => Hash::make('Password12345'),
        ]);

        CorrectionRequest::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'topic' => 'correction',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'council_name' => 'Westminster City Council',
            'council_slug' => 'westminster',
            'page_url' => 'https://knowmycouncil.uk/councils/westminster',
            'source_url' => 'https://example.com/source',
            'details' => 'Please review the record.',
            'status' => 'pending',
        ]);

        $this->actingAs($superadmin)
            ->getJson('/api/admin/ingestion-summary')
            ->assertOk()
            ->assertJsonPath('counts.correction_requests', 1)
            ->assertJsonPath('counts.pending_correction_requests', 1)
            ->assertJsonPath('recent_correction_requests.0.council_slug', 'westminster');
    }

    private function bootstrapXsrfToken(): string
    {
        $response = $this->get('/api/auth/csrf-cookie');
        $response->assertOk()->assertCookie('XSRF-TOKEN');

        $cookie = collect($response->headers->getCookies())
            ->first(fn (Cookie $cookie): bool => $cookie->getName() === 'XSRF-TOKEN');

        $this->assertInstanceOf(Cookie::class, $cookie);

        return rawurldecode($cookie->getValue());
    }
}
