<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Stache;
use Tests\TestCase;

class QuantumLabKitTest extends TestCase
{
    public function test_homepage_renders_quantumlab_content_and_native_navigation(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertOk()->assertSeeText('Advancing safe intelligence for humanity.');
        $response->assertSee('href="/services"', false);
        $response->assertSee('mailto:research@quantumlab.test', false);
        $response->assertDontSee('webflow.js');
    }

    public function test_program_detail_renders_from_a_collection_entry(): void
    {
        $this->withoutVite();

        $response = $this->get('/services/neural-architectures');

        $response->assertOk()->assertSeeText('Neural Architectures');
        $response->assertSeeText('Next-generation sparse transformer & state-space models.');
    }

    public function test_contact_page_uses_a_real_form_with_validation_fields(): void
    {
        $this->withoutVite();

        $response = $this->get('/contact');

        $response->assertOk()->assertSee('method="POST"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="_token"', false);
    }

    #[DataProvider('pageRoutes')]
    public function test_each_content_route_renders_with_a_title(string $route, string $title): void
    {
        $this->withoutVite();

        $this->get($route)->assertOk()->assertSeeText($title);
    }

    /** @return array<string, array{string, string}> */
    public static function pageRoutes(): array
    {
        return [
            'research' => ['/services', 'Research'],
            'models' => ['/cases', 'Models'],
            'grants' => ['/pricing', 'Grants'],
            'publications' => ['/blog', 'Publications'],
            'contact' => ['/contact', 'Contact'],
            'neural arch' => ['/services/neural-architectures', 'Neural Architectures'],
            'quantum sim' => ['/services/quantum-simulation', 'Quantum Simulation'],
            'quantum-7b model' => ['/cases/quantum-7b', 'Quantum-7B'],
            'synapse model' => ['/cases/synapse-xl', 'Synapse-XL'],
            'scaling article' => ['/blog/scaling-laws-quantum-nets', 'Scaling laws in quantum neural networks'],
            'multiagent article' => ['/blog/multi-agent-cooperation', 'Cooperative dynamics in multi-agent systems'],
        ];
    }

    public function test_invalid_quote_is_rejected_by_server_validation(): void
    {
        $this->postJson('/!/forms/quote', [
            'email' => 'invalid-email',
            'message' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_valid_quote_is_stored_and_honeypot_submission_is_discarded(): void
    {
        Mail::fake();
        $submissionDirectory = sys_get_temp_dir().'/quantumlab-forms-'.bin2hex(random_bytes(8));
        Stache::store('form-submissions')->directory($submissionDirectory.'/');

        try {
            $details = [
                'name' => 'QuantumLab Researcher',
                'email' => 'researcher@university.edu',
                'message' => 'Applying for compute grant on Quantum-7B.',
            ];

            $this->postJson('/!/forms/quote', $details)
                ->assertOk()->assertJson(['success' => true, 'submission_created' => true]);

            $submissions = FormSubmission::whereForm('quote');
            $this->assertCount(1, $submissions);
            $this->assertSame($details['email'], $submissions->first()->get('email'));
            $this->assertFileExists($submissions->first()->path());

            $this->postJson('/!/forms/quote', [...$details, 'website' => 'spam.example.com'])
                ->assertOk()->assertJson(['success' => true, 'submission_created' => false]);

            $this->assertCount(1, FormSubmission::whereForm('quote'));
            Mail::assertNothingSent();
        } finally {
            File::deleteDirectory($submissionDirectory);
        }
    }

    public function test_missing_page_shows_the_custom_not_found_page(): void
    {
        $this->withoutVite();

        $this->get('/missing-quantumlab-page')->assertNotFound()->assertSeeText('404');
    }
}
