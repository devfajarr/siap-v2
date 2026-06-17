<?php

namespace Tests\Feature;

use App\Events\FeederSyncProgress;
use App\Jobs\FeederSyncJob;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FeederSyncQueueTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::firstOrCreate(
            ['email' => 'admin_test@test.com'],
            [
                'nama' => 'Admin Test',
                'password' => bcrypt('password'),
                'no_telephone' => '081234567890',
            ]
        );
    }

    public function test_pull_prodis_route_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.feeder.pull-prodis'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Sinkronisasi program studi telah dimulai di latar belakang.',
        ]);

        Queue::assertPushed(FeederSyncJob::class, function (FeederSyncJob $job): bool {
            return $job->type === 'pull-prodis';
        });
    }

    public function test_pull_dosens_route_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.feeder.pull-dosens'));

        $response->assertStatus(200);
        Queue::assertPushed(FeederSyncJob::class, function (FeederSyncJob $job): bool {
            return $job->type === 'pull-dosens';
        });
    }

    public function test_pull_matkuls_route_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.feeder.pull-matkuls'));

        $response->assertStatus(200);
        Queue::assertPushed(FeederSyncJob::class, function (FeederSyncJob $job): bool {
            return $job->type === 'pull-matkuls';
        });
    }

    public function test_pull_mahasiswas_route_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.feeder.pull-mahasiswas'));

        $response->assertStatus(200);
        Queue::assertPushed(FeederSyncJob::class, function (FeederSyncJob $job): bool {
            return $job->type === 'pull-mahasiswas';
        });
    }

    public function test_push_mahasiswa_route_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.feeder.push-mahasiswa', ['id' => 123]));

        $response->assertStatus(200);
        Queue::assertPushed(FeederSyncJob::class, function (FeederSyncJob $job): bool {
            return $job->type === 'push-mahasiswa' && $job->params['mahasiswa_id'] == 123;
        });
    }

    public function test_push_mahasiswa_kelas_route_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.feeder.push-mahasiswa-kelas', ['kelas_id' => 456]));

        $response->assertStatus(200);
        Queue::assertPushed(FeederSyncJob::class, function (FeederSyncJob $job): bool {
            return $job->type === 'push-mahasiswa-kelas' && $job->params['kelas_id'] == 456;
        });
    }

    public function test_sync_job_broadcasts_progress(): void
    {
        Event::fake();

        Http::fake([
            '*/ws/sandbox2.php' => function ($request) {
                $body = json_decode($request->body(), true);
                if ($body['act'] === 'GetToken') {
                    return Http::response(['data' => ['token' => 'token']], 200);
                }
                if ($body['act'] === 'GetCountProdi') {
                    return Http::response(['data' => 2], 200);
                }
                if ($body['act'] === 'GetProdi') {
                    return Http::response(['data' => []], 200);
                }

                return Http::response(['data' => []], 200);
            },
        ]);

        $job = new FeederSyncJob('pull-prodis');

        // Execute the job synchronously
        dispatch_sync($job);

        // Assert that progress events were broadcasted
        Event::assertDispatched(FeederSyncProgress::class);
    }
}
